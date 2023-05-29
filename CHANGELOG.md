# Development Notes

## First Session - 2023-05-25

- [X] Basic setup of the repository;
- [X] Cloned the repo of the child theme of Understrap;
- [ ] Added the inc folder to the autoload of the composer.json file;
- [ ] Added unit tests for the theme using the wp cli <https://developer.wordpress.org/cli/commands/scaffold/theme-tests/>


/Users/annduque/Library/Application Support/Local/run/A-fM3n2tv/mysql/mysqld.sock
bin/install-wp-tests.sh wp_tests root root "localhost:/Users/annduque/Library/ApplicationSupport/Local/run/A-fM3n2tv/mysql/mysqld.sock"

Fontes:
- <https://textcraft.net/style/Textcraft/pokemon>
- <https://flatuicolors.com/palette/defo> Alizarin Red
- <https://www.figma.com/community/file/1044316192441037087>
- <https://www.figma.com/file/hBymWQkHSv9NLKEMuN6Jye/Untitled?type=design&node-id=6%3A5&t=4ShQHcy9Ay3p72cP-1> Base Figma Template
- Inspiration: <https://i.pinimg.com/originals/46/c6/69/46c6696c0e9dd722ef29baaf0ec621f1.png>
- <https://packagist.org/packages/league/color-extractor> Color Extractor Library
- Bootstrap Docs:
  - Columns: <https://getbootstrap.com/docs/5.3/layout/columns/>
  - Container <https://packagist.org/packages/illuminate/container>


- Loom Sessions
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

So, the plan for refactoring is not really that complicated.
Right now, all of our logic is pretty much concentrated on the class-pokefever.php class.
We'll start to break it down into separate components, and make sure the main "provider" is swappable
so we can add new providers later on (which will do with the Digimon API provider).

For the next sessions I'm not at my desk, so I can't really record the camera and mic, but whenever necessary, I'll add notes here.

Let's begin.

First, I'll install a couple of components I want to make use of. The first is the laravel illuminate/container
package, which will allow us to register dependencies and resolve them dynamically whenever necessary.
It also allows us to have a better way to manage singletons, when necessary.

I'll also add a helper file that we can add our functions to.
