<p align="center"><img src="https://raw.githubusercontent.com/aanduque/pokefever/main/img/logo.png" width="320" height="auto"></p>

#### See: [Online Version](https://demos.understrap.com)

# Pokefever

Technical challenge for Fever.

## Objectives

- [X] You should use Understrap as a base template and will use the PokéAPI to fetchsome data.
- [X] We highly recommend the use of TypeScript for the frontend code.
- [X] Understrap mix"underscores" with "bootstrap”.
- [X] Customizing the base appearance of the template is completely optional.
- [X] PHP version 8 must be used.
- [X] You should avoid using ACF.
- [X] You should avoid using builder plugins like Elementor or WPBakery.
- [X] You should provide a repository to facilitate the code review process.

## Requirements

The requirements, as laid out by the document received, are listed below. I've taken the liberty of breaking the list into the required ones and the optional ones.

I've also added a few comments of my own for each items, with [Notes](#notes) explaining my thought process and the decisions I made, as well as some of the challenges I faced and what are the next steps I would take if I had more time.

### Required

- [X] Create a custom post type called “Pokémon” whose slug should be “pokemon”. [Notes](#items-1-2-and-3)
- [X] This post type must contain the following properties [Notes](#items-1-2-and-3):
  - [X] Photo of the pokemon;
  - [X] Pokemon name;
  - [X] pokemon description;
  - [X] Primary and secondary type of pokemon;
  - [X] Pokemon weight;
  - [X] Pokedex number in older version of the game (you can find this info in the api);
  - [X] Pokedex number in the most recent version of the game (you can find this info in the api);
  - [X] _(Optional)_ The attacks of said pokémon with its short description (in English). Said attacks must be stored as desired, considering efficiency and possible reuse;
- [X] Generate 3 pokemon manually with the data requested in point 2 using the PokéAPI [Notes](#items-1-2-and-3);
- [X] Create a template for the custom post type "pokemon" and display; [Notes](#item-4)
  - [X] Photo of the pokemon
  - [X] Pokemon name
  - [X] Pokemon description
  - [X] Pokémon types (primary and secondary)
  - [X] Number of the pokedex in the most recent version and the name of the game
  - [X] Button in which, when clicked, an AJAX call is made to WordPress to show the number of the pokedex of said pokemon in the oldest version of the game with the name of said version. [Note](#item-4-button)
  - [X] Table of movements of the pokemon with two columns: movement name and movement description

### Optional

- [X] Create a pokémon filter (TypeScript): Initially, this page will show a grid with the photos of the pokémon stored in the database. The user must be able to filter by type (the first 5 types returned by PokéAPI). When a filter is selected it should hide the photos of the pokemon whose first or second type does not match the selected filter. Limit to 6 pokemon per page.
- [X] Create a custom url (for example http:/localhost/random) that shows a random pokémon stored in the database. Said URL must redirect to the permanent link of the returned pokémon.
- [X] Create a custom url (for example <http://localhost/generate>) that when summoned will spawn a random pokemon by calling the PokéAPI. It can only be invoked by users who have post creation permissions or higher. This generated pokemon must be stored in WordPress as if it were a manually created post with the same data specified in point 2.
- [X] Using the Wordpress REST API, generate an endpoint to list stored pokémon showing as ID the pokédex number in the most recent version of the game. Generate another endpoint to consult the data of the pokemon requested in point 2 in JSON format.
- [X] Would it be possible to implement DAPI (or other similar APIs) in the developed solution? If so, what changes and abstractions would you propose in the different layers of the application to facilitate said integration? (the implementation is optional)
- [X] The instance becomes more and more popular and starts receiving a lot of traffic generating heavy db usage. What would you do in this situation?

## Notes

### Items 1, 2 and 3

> Create a custom post type called “Pokémon” whose slug should be “pokemon”.
>
> This post type must contain the following properties [...]
>
> Create 3 pokemon manually with the data requested in point 2 using the PokéAPI.

**No surprises on these items.**

As you'll see during the code review, the piece in charge of creating the custom post types and the associated taxonomies is the main theme class `Pokefever\Pokefever`, based on whatever post types are being described by each provider.

The code is designed to support multiple "monster" providers. We have added the `Pokemon` and started to implement the `Digimon` provider as well.

Adding new providers is as simple as creating a new class that implements the `Pokefever\Contracts\Monster_Provider` contract and implementing the required methods.

Then, we just need to make sure we register the provider using the `Pokefever\Pokefever::register_provider` method in one of our registered features.

It sound more complicated than it is, but it's actually pretty simple. We'll elaborate more on this later.

I have to admit that the I was not entirely sure on how to make sure I've completed the third requirement (item 3) on the list, as it sounds like a preparation step that I'd only do it during the first steps of working on the implementation.

### Item 4

> Create a template for the custom post type "pokemon" and display [...]

This was done using the `single.php` template file.

Initially, I had a `single-pokemon.php` template file, but I decided to use the `single.php` template file instead, as it's more generic and it allows us to use the same template for all the different monster providers.

My assumption is that the different monster providers will have different post types, but they will all share the same template, and that this "site/app" will not host regular posts or pages. Even if that was the case, though, new single templates could be added to the theme to support them (e.g. `single-post.php`, `single-page.php`, etc), while the default `single.php` template would be used for the monster providers only.

#### Item 4 Button

For the button that loads the oldest version of the pokemon, I've decided to use the `wp_localize_script` function to pass the URL of the REST API endpoint to the JavaScript code.

My first implementation used jQuery to make the AJAX call. I had plans to refactor it to use fetch and completely get rid of jQuery as one of the dependencies of the theme, but I ran out of time. As a result the button still uses jQuery to make the AJAX call.

It can be seen on the `single` page for each pokemon. [Demo](https://share.cleanshot.com/wW6hslRY)

### Item 5

> Create a pokémon filter (TypeScript): Initially, this page will show a grid with the photos of the pokémon stored in the database. The user must be able to filter by type (the first 5 types returned by PokéAPI). When a filter is selected it should hide the photos of the pokemon whose first or second type does not match the selected filter. Limit to 6 pokemon per page.

This was implemented on the file `src/js/custom-javascript.ts`.

I ended up using the default `custom-javascript.ts` file that comes with the Understrap theme, as it was already being loaded by the theme, and it was already being compiled by the theme's build process. I did have to setup TypeScript so it would work with the theme's build process, though.

If I had a bit more time, I would completely replace the build process of Understrap with a more modern one, like Webpack, and I would use that to compile the TypeScript code instead adding the rollup typescript plugin. This is mostly due to the fact that the current build process got significantly slower after I added the TypeScript plugin (this can actually be seen on some of the loom recordings).

I would also break the `custom-javascript.ts` file into multiple files, to make sure we can only load the code that we need on each page, as well as maintain good separation of concerns.

The filter itself uses an approach that is different from the usual one.

Instead of making an ajax call to an endpoint that returns JSON data, I've decided to simply capture the form submission sent to the same data, using default query parameters that are already recognized by WordPress.

Then, when a valid result is received, I simply replace the HTML markup of the container that holds the pokemon cards with the new HTML markup that was returned by the server. This approach is commonly know as HTML-over-the-wire, and is becoming increasingly popular, with tools such as [Hotwire](https://hotwire.dev/) and [Laravel Livewire](https://laravel-livewire.com/) being the most famous implementations.

One advantage of this approach is that we don't need to worry about the markup of the cards, as it's already being generated by WordPress. We just need to make sure we have a way to capture the form submission and replace the HTML markup of the container that holds the cards.

Another one is that it makes the filter function work even if JavaScript is disabled on the browser, as it will simply default to the default behavior of the form submission.

## Other notes

Full documentation for this starter theme is available at [docs.understrap.com](https://docs.understrap.com).

## Possible questions

For support requests and bugs, we recommend browsing our issues [here (parent theme)](https://github.com/understrap/understrap/issues) and [here (child theme)](https://github.com/understrap/understrap-child/issues) and opening a new issue if necessary. For more broad discussion, like questions about the roadmap, visit our [discussion board](https://github.com/understrap/understrap/discussions).

## Features

- Combines Underscore’s PHP/JS files and Bootstrap’s HTML/CSS/JS.
- Comes with Bootstrap v5 Sass source files and additional .scss files. Nicely sorted and ready to add your own variables and customize the Bootstrap variables.
- Uses sass and postCSS to handle compiling all of the styles into one style sheet. The theme also includes rollup.js to handle javascript compilation and minification.
- Uses a single minified CSS file for all the basic stuff.
- [Font Awesome](http://fortawesome.github.io/Font-Awesome/) integration (v4.7.0)
- Jetpack ready
- WooCommerce support
- Contact Form 7 support
- Translation ready

## How to test it

****

## Installation

## License

Copyright 2023 [Arindo Duque](https://arindoduque.com).

Pokefever is distributed under the terms of the GNU GPL version 2

<http://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html>

## References

- Inspiration for the single page:
  - <https://textcraft.net/style/Textcraft/pokemon>
  - <https://i.pinimg.com/originals/46/c6/69/46c6696c0e9dd722ef29baaf0ec621f1.png>
- Bootstrap Docs:
  - Columns: <https://getbootstrap.com/docs/5.3/layout/columns/>
  - Container <https://packagist.org/packages/illuminate/container>

## Credits

- Illuminate Container: <https://packagist.org/packages/illuminate/container>
- Illuminate Validation: <https://packagist.org/packages/illuminate/validation>
- League Color Extractor: <https://packagist.org/packages/league/color-extractor>
- Understrap: <https://understrap.com/>
- Understrap Child Theme: <https://github.com/understrap/understrap-child>
- Bootstrap: <https://getbootstrap.com/>
- Figma Bootstrap 5 UI Kit: <https://www.figma.com/community/file/1044316192441037087>
- FlatUI Colors: 
  - <https://flatuicolors.com/>
  - <https://flatuicolors.com/palette/defo>
