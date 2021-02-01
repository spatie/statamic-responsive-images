let mix = require("laravel-mix");

mix.js("resources/js/responsive.js", "dist/js").vue();
mix.styles("resources/css/responsive.css", "dist/css/responsive.css");
