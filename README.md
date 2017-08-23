# Nette Tactician integration

Nette extension for the Tactician library
[https://github.com/thephpleague/tactician/](https://github.com/thephpleague/tactician/)

## Installation

### Step 1: Download Nette extension for Tactician
Open a command console, enter your project directory and execute the
following command to download latest version:

```bash
$ composer require cellar/tactician
```

### Step 2: Enable the extension
Open your application config file and add TacticianExtension:

```neon
extensions: 
    tactician: Cellar\Tactician\DI\TacticianExtension
```

### Step 3: Configure middlewares

```neon
tactician:
    commandbus:
        default:
            middleware:
                - @tactician.middleware.queue
                - @tactician.middleware.locking
                - @tactician.middleware.command_handler
```

**Important**: Adding your own middleware is absolutely encouraged, just be sure to always add `@tactician.middleware.command_handler` as the final middleware. Otherwise, your commands won't actually be executed.

Check the [Tactician docs](http://tactician.thephpleague.com/middleware) for more info and a complete list of middleware.

### Step 4: Add command handlers

```neon
services:
    myCommandHandler: 
        class: My\LaunchRocketHandler
        tags: 
            tactician.handler: [
                command: My\LaunchRocketCommand
            ]
```