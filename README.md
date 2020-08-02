> This plugin provides base content and API functionality that can be built off of

# RESTful API

This plugin is a simple but most powerful **RESTful API** which also has extended features like **IP Blacklist** and **Scheduled Shutdown**


**RESTful API** is the best plugin for automated Imports/Exports from your website. This plugin is not limited by standard rules (like only specific responses). You'll have the best dynamic **RESTful API**, and you are free to create unlimited **Request Mappings**.

## What is Request Mapping?
**Request Mapping** is the key to defining your way of importing or exporting the datas for your website. With mapping, you can select and export partial columns of a table with selecting index keys or other keys from related dropdown menu.


## IP Blacklist System
This Web Service has an internal IP Blacklist system which supports both **IPv4** and **IPv6**. You can define unlimited IP's or Notations. It supports multiple types of IP Notations. You can add **IP Ranges** with also **Mask bits** in **CIDR Notation**


## Scheduled Shutdown
If you need to shutdown API for a specific time (i.e. when planning to an System Maintenance), **RESTful API** can disable itself when the shutdown time comes, and than re-activate when the time comes to end.


## Available Settings
- Switch on/off API
- API Log Tracking
- Allowing Direct Table Access
- Access with API Administrator Key
- Unlimited External API Keys (Flexible as your needs)
- Authenticate API only with User Credentials
- Changeable Default Response Output Style
- Changeable Default Response Charset Encoding
- "JSON_UNESCAPED_UNICODE" Flag Support
- CDATA Wrappers in XML Support
- Purgeable API Logs After a Period
- Scheduled Shutdown based your Server
- Shutdown API for a Time Interval, than re-activating Setting
- API IP Blacklist


## Detailed Request Mapping Creations
- URL Parameter "req" for requesting
- Related Table selection
- Columns in Response selection for partial responses
- "Readonly" option for making mapping only accepts "read" and "read_all" requests
- Allow Requesting with Index Keys for Simplicity
- Limit Response Results Option (For read_all)
- Fetch Data Order Option (ASC/DESC)
- Cache duration


## Request Logs Tracking
- Log Status (200 for Successful, Specific header for un-successful)
- Used Method (GET/POST/PUT/DELETE)
- Time Spent for Request
- Request URL
- Browser Info
- Referer Info
- Request Captured DateTime
- Used Key on Request


# Dashboard Report Widgets
## Dashboard API Statistics
- Option to show total statistics
- Option to show detailed statistics


## Dashboard API Logs
- Option to log counts to show
- Option to filter by GET/POST
- Option to filter by Status Code
- Option to filter by Time Spent

# Content

This provides some core content functionlaity such as user actions, extensions to the user models and more.

## User actions

 - Follow/Unfollow
  - Providing follow model and relations
 - Favorite
 - Rating

## Features

 - Events
 - Categories
  - Create nested categories
 - Features
  - Create featured content
 - Dynamic content
  - Ability to fetch dynamic content by eager loading only necessary data
 - Activity
  - Create activity feed based on user activity

## Detailed Changelog
* [Changelog Page](https://gitlab.com/nitm/octobercms-base/wiki)


## Requirements
*  [RainLab User Plugin](http://octobercms.com/plugin/rainlab-user)
*  [Drivers Plugin](http://octobercms.com/plugin/drivers)