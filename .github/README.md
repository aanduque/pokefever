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

The requirements, as laid out by the document received, are listed below. I've taken the liberty of breaking the list into the required ones and the optional ones. I've also added a few notes of my own after each of the items, with notes explaining my thought process and the decisions I made, as well as some of the challenges I faced and what are the next steps I would take if I had more time.

### Required

- [X] Create a custom post type called “Pokémon” whose slug should be “pokemon”.
- [X] This post type must contain the following properties:
    1. Photo of the pokemon;
    2. Pokemon name;
    3. pokemon description;
    4. Primary and secondary type of pokemon;
    5. Pokemon weight;
    6. Pokedex number in older version of the game (you can find this info in the api);
    7. Pokedex number in the most recent version of the game (you can find this info in the api);
    8. (Optional) The attacks of said pokémon with its short description (in English). Said attacks must be stored as desired, considering efficiency and possible reuse;

#### Notes on itens 1 and 2

No surprises on this itens. As you'll see during the code review, the piece in charge of creating the custom post types and the associated taxonomies is the main theme class `Pokefever\Pokefever`, based on whatever post types are being described by each provider.

As we'll elaborate later, the code is designed to support multiple "monster" providers. We have added the `Pokemon` and started to implement the `Digimon` provider as well.

Adding new providers is as simple as creating a new class that implements the `Pokefever\Contracts\Monster_Provider` contract and implementing the required methods. 

Then, we just need to make sure we register the provider using the `Pokefever\Pokefever::register_provider` method in one of our registered features.

It sound more complicated than it is, but it's actually pretty simple. We'll elaborate more on this later.

- [ ] Generate 3 pokemon manually with the data requested in point 2 using the PokéAPI.
- [ ] Create a template for the custom post type "pokemon" and display:
    1. Photo of the pokemon
    2. Pokemon name
    3. c. Pokemon description
    4. Pokémon types (primary and secondary)
    5. Number of the pokedex in the most recent version and the name of the game
    6. Button in which, when clicked, an AJAX call is made to WordPress to show the number of the pokédex of said pokémon in the oldest version of the game with the name of said version.
    7. g. Table of movements of the pokémon with two columns: movement name and movement description

### Optional

- [ ] Create a pokémon filter (TypeScript): Initially, this page will show a grid with the photos of the pokémon stored in the database. The user must be able to filter by type (the first 5 types returned by PokéAPI). When a filter is selected it should hide the photos of the pokemon whose first or second type does not match the selected filter. Limit to 6 pokemon per page.

- [ ] Create a custom url (for example http:/localhost/random) that shows a random pokémon stored in the database. Said URL must redirect to the permanent link of the returned pokémon.

- [ ] Create a custom url (for example <http://localhost/generate>) that when summoned will spawn a random pokemon by calling the PokéAPI. It can only be invoked by users who have post creation permissions or higher. This generated pokemon must be stored in WordPress as if it were a manually created post with the same data specified in point 2.

- [ ] Using the Wordpress REST API, generate an endpoint to list stored pokémon showing as ID the pokédex number in the most recent version of the game. Generate another endpoint to consult the data of the pokemon requested in point 2 in JSON format.

- [ ] Would it be possible to implement DAPI (or other similar APIs) in the developed solution? If so, what changes and abstractions would you propose in the different layers of the application to facilitate said integration? (the implementation is optional)

- [ ] The instance becomes more and more popular and starts receiving a lot of traffic generating heavy db usage. What would you do in this situation?

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
