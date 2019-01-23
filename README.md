## Introduction

**electron-pdf-php** is a PHP wrapper of [electron-pdf](https://github.com/fraserxu/electron-pdf).
It simply provides a PHP interface for the `electron-pdf` cli tool.

## Requirements

- Linux or MacOS
- Electron PDF

## Installation

First install **electron-pdf** globally:

    npm install -g electron-pdf
    
Require **electron-pdf-php** using composer:

    composer require silvioiannone/electron-pdf-php
    
## Usage

Usage is super easy:

    $generator = new SI\ElectronPdfPhp\Generator();
    $pdf = $generator->fromHtml($html)
        ->content();
        
### Settings

When instantiating a new generator instance the following settings may be passed to the constructor:

- `executable` => `string` - Path to the electron-pdf executable. Default: 'electron-pdf'
- `proxyWithNode` => `bool` - Execute the command using node. This may be necessary in some cases 
   where the error `env: node: command not found` is thrown. Default: false
- `graphicalEnvironment` => `bool` - Whether the server has a graphical environment. This is only
   valid on Linux machines that have 
   [Xvfb](https://www.x.org/archive/X11R7.6/doc/man/man1/Xvfb.1.xhtml) installed. Default: false
- `marginsType` => `int` - Specify the type of margins to use:
    - `0`: default margins
    - `1`: no margins (electron-pdf default setting)
    - `2`: minimum margins

Example:

    $generator = new SI\ElectronPdfPhp\Generator([
        'executable' => '/usr/local/bin/electron-pdf'
        ...
    ]);
   
### Specify a source

Generate a PDF from a URL:
    
    // Generate the PDF from a URL
    $pdf = $generator->from('http://google.com')
        ->content();
        
Generate a PDF from HTML:

    // Generate the PDF from HTML
    $pdf = $generator->fromHtml('<h1>Test<h1>')
        ->content();

### Specify a destination

Save the PDF to a file:

    $generator->from('http://google.com')
        ->to('/home/downloads/test.pdf');

Get the PDF contents:

    $pdf = $generator->from('http://google.com')
        ->content();
