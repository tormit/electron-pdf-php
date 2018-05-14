<?php

namespace SI\ElectronPdfPhp;

use SI\ElectronPdfPhp\Exceptions\GenerationFailed;
use SI\ElectronPdfPhp\Exceptions\MissingDestination;
use SI\ElectronPdfPhp\Exceptions\MissingSource;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * PDF generator.
 */
class Generator
{
    /**
     * PDF source location.
     *
     * @var string
     */
    protected $from;

    /**
     * HTML that will be converted.
     *
     * @var string
     */
    protected $fromHtml;

    /**
     * Settings.
     *
     * @var array
     */
    protected $settings;

    /**
     * PDF save location.
     *
     * @var string.
     */
    protected $to;

    /**
     * Generator constructor.
     *
     * Settings:
     *
     *     [
     *         'executable' => (string). Path to the electron-pdf executable. Default:
     *                         'electron-pdf'.
     *         'proxyWithNode' => (bool). Execute the command using node. This may be necessary in
     *                            some cases where the error env: node: command not found` is
     *                            thrown. Default: false.
     *         'graphicalEnvironment' => (bool) Whether the server has a graphical environment. This
     *                                   is only valid on Linux machines that have Xvfb installed.
     *                                   Default: false.
     *     ]
     *
     * @param array $settings Instance settings.
     */
    public function __construct(array $settings = [])
    {
        $defaultSettings = [
            'executable' => 'electron-pdf',
            'proxyWithNode' => false,
            'graphicalEnvironment' => false
        ];

        $this->settings = array_merge($defaultSettings, $settings);
    }

    /**
     * Set the PDF source.
     *
     * @param string $url
     * @return Generator
     */
    public function from(string $url): Generator
    {
        $this->from = $url;

        return $this;
    }

    /**
     * Set the HTML source that will be converted.
     *
     * @param string $html
     * @return Generator
     */
    public function fromHtml(string $html): Generator
    {
        $this->fromHtml = $html;

        return $this;
    }

    /**
     * Set where the PDF file save location.
     *
     * @param string $destination
     * @return Generator
     */
    public function to(string $destination): Generator
    {
        $this->to = $destination;

        return $this;
    }

    /**
     * Generate the PDF and return its content.
     *
     * @return string
     */
    public function content(): string
    {
        // Set the `to` to a temporary file.
        $this->to = $this->makeTemporaryFile();
        file_put_contents($this->to, 'test');

        $this->generate();

        return file_get_contents($this->to);
    }

    /**
     * Generate the PDF.
     *
     * @return void
     */
    public function generate(): void
    {
        if (! $this->from && ! $this->fromHtml) {
            throw new MissingSource();
        }

        if (! $this->to) {
            throw new MissingDestination();
        }

        if ($this->fromHtml) {
            $this->prepareGenerationFromHtml();
        }

        $this->run();
    }

    /**
     * Execute the pdf generation process.
     *
     * @throws GenerationFailed
     * @return void
     */
    protected function run(): void
    {
        if (! $this->settings['graphicalEnvironment']) {
            $this->prepareVirtualFrameBuffer();
        }

        $process = $this->createProcess();
        try {
            $process->mustRun();
        } catch (ProcessFailedException $exception) {
            throw new GenerationFailed($this->from, $this->to, $process);
        }
    }

    /**
     * Prepare the virtual frame buffer for environments without graphical environment.
     *
     * @return void
     */
    protected function prepareVirtualFrameBuffer(): void
    {
        (new Process('export DISPLAY=\':99.0\''))->mustRun();
        (new Process('Xvfb :99 -screen 0 1024x768x24 > /dev/null 2>&1 &'))->mustRun();
    }

    /**
     * Create the electron-pdf process.
     *
     * @return Process
     */
    protected function createProcess(): Process
    {
        $command = [
            $this->settings['executable'],
            $this->from,
            $this->to
        ];

        // If we need to proxy with node we just need to prepend the $command with `node`.
        if ($this->settings['proxyWithNode']) {
            array_unshift($command, 'node');
        }

        // If there's no graphical environment we need to prepend the $command with `xvfb-run -n 9`.
        if (! $this->settings['graphicalEnvironment']) {
            array_unshift($command, '9');
            array_unshift($command, '-n');
            array_unshift($command, 'xvfb-run');
        }

        return new Process($command);
    }

    /**
     * Prepare the PDF generation from the HTML content.
     *
     * @return void
     */
    protected function prepareGenerationFromHtml(): void
    {
        // Save the HTML in a temporary file and then set the from url.
        $file = $this->makeTemporaryFile();

        file_put_contents($file, $this->fromHtml);

        $this->from = $file;
    }

    /**
     * Clean up.
     *
     * @return void
     */
    protected function clean(): void
    {
        if ($this->fromHtml) {
            // Delete the temporary file
            unlink($this->from);
        }
    }

    /**
     * Generate a temporary file with an unique name.
     *
     * @return string
     */
    protected function makeTemporaryFile(): string
    {
        return DIRECTORY_SEPARATOR . trim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) .
            DIRECTORY_SEPARATOR . ltrim(uniqid('epp', true), DIRECTORY_SEPARATOR);
    }
}
