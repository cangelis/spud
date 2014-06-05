# Spud : Template development made easy

You are developing an HTML template that contains hundreds of pages and you need to modify the footer from all of them one by one.
Very bad yeah? You don't need to modify hundreds of pages with Spud. Spud is simply a template engine tool that makes your template development easier.

## Installation

Spud is developed with PHP so you need PHP (at least 5.4) to be installed on your computer.
Then, [download](http://www.cangelis.com/spud.phar) `spud.phar` and run `spud.phar --version` to see if it works.

## An example

**layouts/layout.spud.html**

```html
<html>
    <head>
        <title>My Template | @yields(title)</title>
    </head>
    <body>
        @yields(content)
        @include(footers.footer)
    </body>
</html>
```

**datepickers.spud.html**
```html
@extends(layouts.layout)
    @section(title)
        DatePickers
    @endsection
    @section(content)
        <!-- some datepicker code here -->
    @endsection
@endextends
```

**footers/footer.spud.html**
```html
<footer>&copy; My Template</footer>
```

Then you need to call `spud.phar build`. And then your `datepickers.spud.html` is rendered and turns into this (check out the `compiled` folder):

```html
<html>
    <head>
        <title>My Template | DatePickers</title>
    </head>
    <body>
        <!-- some datepicker code here -->
        <footer>&copy; My Template</footer>
    </body>
</html>
```

## Documentation

All files that will be compiled, have to have `.spud.html` extension, otherwise they will not be compiled.

Spud has two options. You can `@include` a file or `@extends` a layout. You can see the examples above.

Command line usage:

`php spud.phar build [--input[="..."]] [--output[="..."]]`

`input`: the folder that will be compiled.
`output`: the folder in which contains the compiled files.

## Contribution

Feel free to contribute. Please follow PSR-2 standards.