# Environment Version Manager

A tool for developers using Windows which allows you to quickly switch between PHP environments.

### What does this tool do?

- Downloads & installs PHP releases for Windows
    - Allow you to quickly select extensions you wish to enable
    - Automatically sets certs for curl
- Seamlessly switch between PHP versions all from the command line

# Prerequisites

This tool assumes a couple of things:

1. You have [Composer](https://getcomposer.org/) installed
2. You have PHP v7.3 or greater installed

# Installation & Update

This package is installed as a global package:

```
composer global require getevm/evm
```

and from time to time we'll update this package:

```
composer global update getevm/evm
```

Until a stable version is released, you'll need to specify `:dev-master` after the package name.

# Usage

The idea is that this package will support a variety of different environment dependencies and each one will have its
own subcommands:

```bash
evm <dependency> <cmd>
```

**Note:** This may never happen.

### PHP

The basic syntax for the command is:

```bash
$ evm php <cmd> <?version> --ts --archType=<x86|x64>
```

- The `--ts` flag refers to thead safety. If omitted it will pull a non-thread safe release
- The `--archType` flag allows you to specify whether to pull a release targeting a specific architecture type. If
  omitted it will try and sniff the architecture of the machine requesting the release and base it off that

The available commands are:

```bash
$ evm php install 8.1.0 # install v8.1.0 non-thread safe

$ evm php install 8.1.0 --ts --archType=x86 # install v8.1.0 thread safe 32bit

$ evm php use 8.1.0 --ts --archType=x64 # use v8.1.0 thread safe 64bit

$ evm php ls # see information about current installed release

$ evm php sync # synchronise version file with the centralized file; used to pull latest PHP releases
```

# Support
