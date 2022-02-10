# MailerLite

[![swagger](https://img.shields.io/badge/documentation-swagger-green)](https://escolalms.github.io/MailerLite/)
[![codecov](https://codecov.io/gh/EscolaLMS/Mattermost/branch/main/graph/badge.svg?token=NRAN4R8AGZ)](https://codecov.io/gh/EscolaLMS/MailerLite)
[![phpunit](https://github.com/EscolaLMS/Mattermost/actions/workflows/test.yml/badge.svg)](https://github.com/EscolaLMS/MailerLite/actions/workflows/test.yml)
[![downloads](https://img.shields.io/packagist/dt/escolalms/mailerlite)](https://packagist.org/packages/escolalms/mailerlite)
[![downloads](https://img.shields.io/packagist/v/escolalms/mailerlite)](https://packagist.org/packages/escolalms/mailerlite)
[![downloads](https://img.shields.io/packagist/l/escolalms/mailerlite)](https://packagist.org/packages/escolalms/mailerlite)
[![Maintainability](https://api.codeclimate.com/v1/badges/00725c6ea461fcfa2754/maintainability)](https://codeclimate.com/github/EscolaLMS/MailerLite/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/00725c6ea461fcfa2754/test_coverage)](https://codeclimate.com/github/EscolaLMS/MailerLite/test_coverage)

This package is used to add users to groups on the MailerLite after dispatching events.
- `EscolaLms\Auth\Events\AccountConfirmed` => add to group of registered users
- `EscolaLms\Cart\Events\CartOrderPaid` => add to group of users with paid orders
- `EscolaLms\Auth\Events\AccountBlocked` => remove from all groups

## Installation
```
composer require escolalms/mailerlite
```


