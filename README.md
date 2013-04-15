# SchemaDiff - Comparse DB schemas

This is a quick tool for comparing two database schemas, but can be used as a web-based diff tool for any two sources.

<a href="https://github.com/iamcal/SchemaDiff/raw/master/demo_large.png"><img src="https://github.com/iamcal/SchemaDiff/raw/master/demo.png" /></a>

## Features

* Visual diff with coloring
* Configurable with `mysqldump`, any unix command or flat files
* Collapsable diff context for sparsely-differing files
* Highlight strings within files for warnings (we use this to show tables with bad encodings or engine types)

## Installation

* Have a server running PHP with access to execute `diff`
* Clone repo onto your server
* Copy `config.php.example` to `config.php` and modify it
* ...
* Profit!
