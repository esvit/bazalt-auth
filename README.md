bazalt-auth
===========
[![Build Status](https://travis-ci.org/esvit/bazalt-auth.png)](https://travis-ci.org/esvit/bazalt-auth) [![Coverage Status](https://coveralls.io/repos/esvit/bazalt-auth/badge.png)](https://coveralls.io/r/esvit/bazalt-auth)

```php
// Security configuration
$config = \Bazalt\Config::container();
$config['jwt.key'] = 'key';
```


## GET /auth/session

Return current logined user

## POST /auth/session

Login user

## DELETE /auth/session

Logout user
