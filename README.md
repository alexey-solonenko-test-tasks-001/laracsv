
**Table of Contents**

[Deployed App](#deployed-app)

[Local](#local)

[Laravel](#laravel)

[CSV](#csv)

[Viewer](#viewer)

[URL-search](#url-search)

[Login&Security](#loging&security)

[Database](#database)

[Tables](#tables)

### Deployed App

Deployed on <a href="http://laracsv.tab4lioz.beget.tech/" target="_blank">a hosting</a>
Please, Ctrl+F5 if there are problems with CDN.

Please, create an account, and login.

Or, user

tab4@live.com
Test2020!

### Local

A default Laravel 7 bootstrapped project. 

### Laravel

I never built a Laravel project from beginning. Local and remote took sometime to setup.

I intentionally left all code placed in one single controller. I'm not yet confident in understanding of the Laravel architecture to distribute the code among service providers, middleware, and etc.

However, I used all the features I managed to get some hold of. Especially, in relation with view and assets, and Eloquent.

### CSV

Script is located <a href="https://github.com/AlexeySolonenko/laracsv/blob/master/storage/app/phpexecs/uploadcsv.php/" target="_blank">here</a>, and has some interactive help. It resolves the file path relative to either storage, or root.

An interface for uploading is provided. Click, drag&drop or from a back-up location on a server.

I don't have much experience writing scripts for CLI, or CLI apps (except cronjobs), however, I can learn if needed.
- arguments as requested by task.
- simple validations and confirms/errors messages, queries also outputted for reference.
- tested on both, Win and Ubuntu (remote server) 


### Viewer

A form and a table with all the filters and groupings requried are provided. Either use a button for a default multi-column ordering, or use `Shift` for your custom one.

You will have a handy `log` tool that lists actions and some of the queries.

Also, for DB testing the is `generate random data` button.

**PLEASE** note, that I dont' have access to all settings on my hosting, so the random data generation, as well as quering large datasets, may fail from time to time. You won't have those constraints on local.

## URL-search
Please, consider a button that generates a link. A link with GET request is consumed by JavaScript, and the results table is reloaded. 

Button copies **last** **executed** query, it does not generate a new `GET` string.

## Login&security
Used Laravel defaults. While they are 'out-of-the-box', it still took time making Laravel work both, on local and remote deployment! I'd much appreciate if it will be accounted for.

## Database

I used default settings of creating a database with MySQL Workbench. I'm working with SQL queries on a daily basis, but I'm not much into DBA, however, I'm interested in developing with it.


### Tables

There are two buttons to delete and re-create the tables. Queries are outputted on the page (queries are stored in JSON in the repo).

Tables deal_types and client_list are small, and, basically, they don't benefit from index at this stage.
However, for future they are indexed for joins and search by var-chars. Queryies for tables creations are avaialble in the log on the page.

Table deals_logs. I don't know the requirements for the deal_logs table. I added a unique key to demonstrate that no duplicates are imported from the file. However, I would keep a logs table without such constraints, and would allow multiple records for the same tstamp.

Regarding indexes, even with a bit of randomly generated data the table well benefits of two indexes added for client_id and deal_type.

Just in case this table needs a better performance for frequent lookups, and if we have enough resources on the server, then, considering all the orderings, groupings and etc. we are doing, the table will be even faster with:

`INDEX mkey (client_id,deal_type,deal_tstamp,deal_accepted,deal_refused)`


