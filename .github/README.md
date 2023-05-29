<p align="center"><img src="https://raw.githubusercontent.com/aanduque/pokefever/main/img/logo.png" width="320" height="auto"></p>

# Pokefever

Technical challenge for Fever.

## Installing Locally

This repository ships with a Docker setup to get you up-and-running as soon as possible.
To setup a local environment to test this out, follow the steps below:

### Clone this repository into your local machine.

```git clone https://github.com/aanduque/pokefever.git```

### Install composer dependencies

```composer install```

### Install node dependencies

```yarn install```

### Build the theme

```yarn build```

### Start the docker containers

```docker-compose up -d```

### You're ready

You should now be able to access the site at <http://localhost:8000>
The `admin` username is `admin` and the password is `admin`.

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

- [X] Create a pokémon filter (TypeScript): Initially, this page will show a grid with the photos of the pokémon stored in the database. The user must be able to filter by type (the first 5 types returned by PokéAPI). When a filter is selected it should hide the photos of the pokemon whose first or second type does not match the selected filter. Limit to 6 pokemon per page. [Notes](#item-5)
- [X] Create a custom url (for example <http::/localhost/random>) that shows a random pokémon stored in the database. Said URL must redirect to the permanent link of the returned pokémon. [Notes](#items-6-and-7)
- [X] Create a custom url (for example <http://localhost/generate>) that when summoned will spawn a random pokemon by calling the PokéAPI. It can only be invoked by users who have post creation permissions or higher. This generated pokemon must be stored in WordPress as if it were a manually created post with the same data specified in point 2. [Notes](#items-6-and-7)
- [ ] Using the Wordpress REST API, generate an endpoint to list stored pokémon showing as ID the pokédex number in the most recent version of the game. Generate another endpoint to consult the data of the pokemon requested in point 2 in JSON format. [Notes](#item-8)
- [X] Would it be possible to implement DAPI (or other similar APIs) in the developed solution? If so, what changes and abstractions would you propose in the different layers of the application to facilitate said integration? (the implementation is optional) [Notes](#item-9)
- [X] The instance becomes more and more popular and starts receiving a lot of traffic generating heavy db usage. What would you do in this situation? [Notes](#item-10)

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

### Items 6 and 7

This was implemented as a base feature class - `Pokefever\Features\Endpoint` - that is extended by two other classes - `Pokefever\Features\Required\Generate_Endpoint` and `Pokefever\Features\Required\Random_Endpoint` - that are in charge of handling the two cases described on the items.

The base class is responsible for registering the endpoint and the callback function that will handle the request.
The handle is called through the container, which is responsible for resolving the dependencies of the method. This allows us, for example, to make sure the handle method is always using the correct provider, even if we decide to change the provider in the future, or the user changes the provider they're seeing on the site (by navigating to a different archive page).

#### Item 8

If I had more time, I would expand the base endpoint class to add support to registering REST_API routes using the same logic. This would allow us to easily register the endpoints described on item 8.

### Item 9

> Would it be possible to implement DAPI (or other similar APIs) in the developed solution? If so, what changes and abstractions would you propose in the different layers of the application to facilitate said integration?

Yes, the current architecture was built to support multiple providers, and it would be easy to add support to a new provider.

I didn't have the time to completely implement the DAPI API, but the code contains an initial implementation of a DAPI Provider at `inc/providers/class-digimon.php`.

Once a new provider is registered, it becomes available on the `homepage` (implemented using the `index.php`) named WordPress template. The user can then select the provider they want to see on the site, and the site will automatically use the correct provider to load the data, including when visiting the `/generate` and `/random` endpoints.

### Item 10

> The instance becomes more and more popular and starts receiving a lot of traffic generating heavy db usage. What would you do in this situation?

Our implementation relies on very little code that is not "WordPress-native".

By using the default WordPress functions and APIs, we can rely on the WordPress' strong ecosystem of plugins and services to help us scale the site.

Examples of where this can make a difference on our case:

#### Caching

Instead of simply linking to the image links returned by the API, we download the images and save them on the WordPress media library. This allows us to use the WordPress' built-in image optimization features, as well as the WordPress' CDN integrations (added via plugins), to make sure the images are served as fast as possible. This also protects our site from breaking if the API goes down, as we will still have the images on our media library.

Plugins such as s3-offload can be used to offload the media library to a CDN, such as AWS S3, which can help us reduce the load on our server and geo-locate the images closer to the user.

#### Taxonomy, CPTs and post meta usage

In that same vein, the fact that we use WordPress Custom Post Type system to save our different monsters, use the meta system to save monster data, and use the taxonomy system to save monster types, allows us to use the WordPress' built-in caching system to cache the data, as well as use plugins such as Redis Object Cache to cache the data on a Redis server.

A persistent object cache can DRAMATICALLY reduce the load on an installation such as ours, as it allows us to keep data on memory-based databases, such as Redis, instead of having to query the database on every request.

Whenever we needed to query the database directly (it only happened once in this project), we are manually adding that query to the object cache, so it can be cached and reused on future requests if an object cache is available (or installed later).

API queries that we know won't change too frequently are also already being cached using the WordPress native transients API. This mechanism was used to cache the total number of pokemon available on the PokeAPI, for example, to prevent us from having to query the API on every /generate request.

With all of those things already in place, we can easily scale our site by simply adding a persistent object cache, such as Redis, and a CDN, such as Cloudflare, Digital Ocean spaces, AWS, etc, to this installation.

## Loom Sessions

As I was developing this project, whenever possible, I recorded my screen and webcam using Loom (despite the fact tha Loom kept crashing on me, I still managed to record most of the sessions).
You can find the recordings here (I should have done a better job of naming the videos, sorry about that):

- Initial Layout <https://www.loom.com/share/ee03ab4a46b4492694c1501a81d9e64b>
- Implementing /generate <https://www.loom.com/share/f08fc521533a499981477d924f47a9f3>
- Implementing /random <https://www.loom.com/share/4ffb639a8fda415dbd2682314311e2dd>
- <https://www.loom.com/share/17372703273849978571c3fb74ec9d14>
- <https://www.loom.com/share/6fca8fbea66642eeb105421a9cc4037d>
- <https://www.loom.com/share/9e6b535e17b7408cbf2725991b0b1595>
- <https://www.loom.com/share/d905cf77718b4b96869159cf5b3e4092>
- <https://www.loom.com/share/bfb39ab2aedb4cd883275cb4b462f229>
- <https://www.loom.com/share/cef5f888dda9425997e81160417ddb75>
- <https://www.loom.com/share/f7b24fd4941f4213bc5c3a386694614e>
- <https://www.loom.com/share/f8ff006b86d440abb039299c401e7950>
- <https://www.loom.com/share/f352d78419934e578860c1175a6b0440>
- <https://www.loom.com/share/8047f8eb55b0423f9e87282565d9ce3f>
- <https://www.loom.com/share/d67d48b05a1a4c2f98a78e7cba07f44c>
- <https://www.loom.com/share/cb6eb162e17f454caefc0f0bdfe9a501>
- <https://www.loom.com/share/9a9a3320f7724133bbaabef4502f6475>
- <https://www.loom.com/share/1ade6047c495494b9295bde2a6cbd723>
- <https://www.loom.com/share/822e34e8f53e4fd0bfe0a420297422b0>
- <https://www.loom.com/share/133a3a70bf78436f912c76af24b3ba9f>
- <https://www.loom.com/share/ee2034ab365c4727934008ec0952a3cd>

## Testing

I wish I had more time to write tests for this project, but I was already running out of time.

I made the decision of not using TDD on this project, as it usually does not work that well with WordPress (as the bootstrap process is very slow, which makes the red-green-refactor cycle very slow as well).

I did setup PHPUnit, later replacing it with Pest, which I much prefer.

If I had more time, I would definitively write more tests, focusing on the following, if total coverage was not possible:

- Test the registered endpoint responses, as well as possible errors;
- Test the generate methods of the providers (although, I'm not sure if it would make sense to mock it, as what we really want to test is the actual API response);

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
