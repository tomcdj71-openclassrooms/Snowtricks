# Welcome to Snowtricks 👋
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](#)
[![Twitter: tomcdj71](https://img.shields.io/twitter/follow/tomcdj71.svg?style=social)](https://twitter.com/tomcdj71)
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/edf0ad3a4e2a46648b7b08b5270d86b7)](https://app.codacy.com/gh/tomcdj71/Snowtricks/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)
[![Codacy Badge](https://app.codacy.com/project/badge/Coverage/edf0ad3a4e2a46648b7b08b5270d86b7)](https://app.codacy.com/gh/tomcdj71/Snowtricks/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_coverage)

> 6th project of my OpenClassrooms courses

## Pre-requisites :
- PHP 8.2
- Composer
- npm/yarn (I used pnpm)
- Symfony CLI
---

## Install

```sh
git clone https://github.com/tomcdj71/Snowtricks
cd Snowtricks
composer install --optimize-autoloader
yarn install --force
yarn build
symfony console d:d:c
symfony console d:m:m
symfony console d:f:l
symfony serve
```

## Features

- [x] Registration
- [x] Authentication
- [x] Password Reset
- [x] Add a trick
- [x] Edit a trick
- [x] Delete a Trick
- [x] Show a Trick
- [x] Comment a Trick
- [x] Change User avatar
- [x] Load more tricks/comments

## Usage

Once you've ran `symfony serve` you can open your browser and go to [http://localhost:8000](http://localhost:8000) and start using the app

## About this project

This project was made with [Symfony 6.3 ](https://symfony.com/releases/6.3) and [PHP 8.2.7](https://www.php.net/ChangeLog-8.php#8.2.7). Design of the the project was made with [TailwindCSS 3.3.2](https://github.com/tailwindlabs/tailwindcss/blob/master/CHANGELOG.md#332---2023-04-25) with a powerful integration of [HeroIcons 2.0.18](https://github.com/tailwindlabs/heroicons/blob/master/CHANGELOG.md#2018---2023-05-09)

A great commit process is in place in order to make better commits, using grumphp, phpmd, rector, phpstan, php-cs-fixer and other tools triggered by a commit hook.
## Author

👤 **Thomas**

* Twitter: [@tomcdj71](https://twitter.com/tomcdj71)
* Github: [@tomcdj71](https://github.com/tomcdj71)

## Show your support

Give a ⭐️ if this project helped you!


***
_This README was generated with ❤️ by [readme-md-generator](https://github.com/kefranabg/readme-md-generator)_
