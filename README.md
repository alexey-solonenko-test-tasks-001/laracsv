
**Table of Contents**

[Deployed App](#deployed-app)

[Local](#local)

[Tables](#tables)

[CSV](#csv)

[Implemented](#implemented)

[Features](#features)

[Missing](#missing)




### Deployed App

Deployed on <a href="http://laracsv.tab4lioz.beget.tech/" target="_blank">a hosting</a>
Please, Ctrl+F5 if there are problems with CDN.

### Local

A default Laravel 7 bootstrapped project


### Tables

There are two buttons to delete and re-create the tables. Queries are outputted on the page (queries are stored in JSON in the repo).

Tables deal_types and client_list are small, and, basically, they don't benefit from index at this stage.
However, for future they are indexed for joins and search by var-chars. Queryies for tables creations are avaialble in the log on the page.

Table deals_logs. I don't know the requirements for the deal_logs table. I added a unique key to demonstrate that no duplicates are imported from the file. However, I would keep a logs table without such constraints, and would allow multiple records for the same tstamp.

Regarding indexes, even with a bit of randomly generated data the table well benefits of two indexes added for client_id and deal_type.

Just in case this table needs a better performance for frequent lookups, and if we have enough resources on the server, then, considering all the orderings, groupings and etc. we are doing, the table will be even faster with:
INDEX `mkey` (client_id,deal_type,deal_tstamp,deal_accepted,deal_refused).

### CSV


## Fulfillment report

### Implemented

- table creation - 2 buttons + Ajax
- import CSV, line-by-line parsing. Sanitation, security checks, tools to adjust performance. Customly written parser by me.
- import CSV - you can either drag&drop a file, or do nothing - the file will be downloaded from a remote host by a default URL.
- Data representation - DataTables + pagination, sorting.
- using POST instead of GET
- filters
- - date range from/to
- - client, deal search

### Features

- DataTables dynamic plugin
- custom PHP
- MySQL query builder lib
- preventing duplicate usernames and deal types

### Missing
- Laravel, worked with Lumen a bit, but did not setup a Laravel app.
- did not have time for login/logout
- did not understand 'group_by' hour/day/month - how should the aggregate data be presented, please? Only accepts/refuses over a given period?
- did not understand what kind of script is required to be provided for DB creation?;



## CLI
Scripts are located in the storage folde.
File upload script: .
Tables management script: .
I don't have much experience writing scripts for CLI, or CLI apps (except cronjobs), however, I can learn if needed.
- arguments as requested by task.
- simple validations and confirms/errors messages, queries also outputted for reference.
- tested on both, Win and Ubuntu (remote server) 


## Login&security
Used Laravel defaults. While they are 'out-of-the-box', it still took time making Laravel work both, on local and remote deployment! I'd much appreciate if it will be accounted for.

## URL-search
Please, consider a button that generates a link. A link with GET request is consumed by JavaScript, and the results table is reloaded. From my experience I'm trying to avoid as much as I can GET requests except for routing and minimum number of data. 

## UI-table

Please, refer to UI and button labels. Please, use Shift for multi-column ordering. The page forms get disabled while an Ajax call is executed.

There are pop-up messages, and a log of actions taken. SQL queries are also outputted in the log.



## Database

I used default settings of creating a database with MySQL Workbench. I'm working with SQL queries on a daily basis, but I'm not much into DBA, however, I'm interested in developing with it.

## Missing

- In my point of view, I'd setup SSL (tls), improve styling, setup a continuous CI/CD pipeline, tests.
- I have a great interest in studying the Laravel architecture more, and to update the business logic part to complay with "Laravel Way".


