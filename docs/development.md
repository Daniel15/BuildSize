<!-- https://buildsize.org/docs/development -->

# Development

This page documents how to configure BuildSize in your local environment for debugging/development purposes.

## Prerequisites

You need to have [PHP 7.1](https://php.net/) and [MySQL](https://www.mysql.com/) (or a MySQL-compatible database such as MariaDB) installed. A webserver does not need to be installed, as BuildSize can use Laravel's built-in development server.

## Webhook accessibility

GitHub needs to be able to call the webhook on your BuildSize instance. You can either use port forwarding to make port `8000` accessible from the internet (use http://canyouseeme.org/ to validate that it's accessible), or use a service like [ngrok](https://ngrok.com/) to create a tunnel:

```
ngrok http 8000
```

## GitHub App

You will need to [create an app on GitHub](https://github.com/settings/apps/new). Use these settings:
 - GitHub App Name: anything. Note that it needs to be globally unique on GitHub. `BuildSize_[username]` is probably fine.
 - Homepage URL: `https://localhost:8000/`
 - User authorization callback URL: `http://localhost:8000/login/github/complete`
 - Setup URL: `http://localhost:8000/setup`
 - Webhook URL: Publicly accessible URL for your app followed by `/webhook/github` (eg. `https://username.dyndns.org:8000/webhook/github` or `https://1234567.ngrok.io/webhook/github`)
 - Webhook secret: Any random string. Remember it for later!

## Configuration

Copy `.env.example` to `.env`, and modify it to reflect settings specific to your environment:
 - `GITHUB_CLIENT_ID` and `GITHUB_CLIENT_SECRET`: Client ID and secret under the "OAuth credentials" section in the GitHub App settings
 - `GITHUB_APP_ID`: ID of the GitHub app, found in the "About" section of the app config
 - `GITHUB_APP_ALIAS`: Last part of the URL for the app (eg. for `https://github.com/apps/buildsize`, the app alias is `buildsize`)
 - `GITHUB_WEBHOOK_SECRET`: Random string you used for the "webhook secret" above
 - `GITHUB_STATUS_CONTEXT_PREFIX`: Prefix to use for commit status messages. This should be unique so you don't confuse dev messages with real ones
 - `DB_DATABASE`, `DB_USERNAME` and `DB_PASSWORD`: MySQL database credentials
 - `APP_KEY`: Randomly-generated encryption key. Run `php artisan key:generate` to set it

## Database Migrations

Run `php artisan migrate` to create the database tables

## Run the Site

Now you should be ready to go! Run `php artisan serve` to start the development server. Try hitting `http://localhost:8000/` and then logging in with GitHub. Hopefully, it should work!
