## Components

`Simplicity is the ultimate sophistication. -Leonardo da Vinci`

There's nothing to do with changing or adding codes or editing layouts.
You just need to add `RESTful API` Component to an empty page which you'll use as API page (i.e. /api page)

> **Note:** Please make sure you added RESTful API Component to an empty page. Component will change the HTTP output Headers, so don't associate API page with any layouts, partials or contents.


## How to edit settings?
- Go to **Settings** menu
- Under **System** tab, you'll find RESTful API configuration page
- On settings page, all configurations also have enough details under each option.


## Settings Details
- `RESTful API Status` - You can change the API status for system based. If you use multiple page API, you can change API status by every component's self attribute
- `RESTful API Log Tracking` - You can enable or disable tracking api request logs
- `Allow Direct Table Access` - This is not a good option but we added for making API full dynamic. This option is experimental for using
- `API Administrator Key` - Master key. This key is intended for your usage only. So don't share this key with aynone..
- `External API Keys` - You can add unlimited keys for giving out to external usage
- `Authenticate API only with User Credentials` - If you want to permit API to only your users, simply activate this option. User credentials should be sent as "api_login" and "api_password" parameters. (Using API Administrator Key or External API Key is faster than User Authentication)
- `Default Response Output Style` - You may prefer your users getting response as JSON / XML as default. If there is a "type" parameter in request, output will be override this option
- `Default Response Charset Encoding` - You can change output character encoding with this option
- `Add -JSON_UNESCAPED_UNICODE- Flag` - JSON_UNESCAPED_UNICODE Flag is useful when API returns thousands (or zillions) of non-Unicode data, or when you prefer "Hello José" to "Hello Jos\u00e9"
- `Add CDATA Wrappers in XML` - If you have HTML content in some response columns, you should activate wrapping with CDATA in XML
- `Purge API Logs After` - You can define days you wish to purge logs after created
- `Scheduled Shutdown` - Place a tick to this option and choose start and end times if you want to shutdown the WebService for specific time interval
- `API IP Blacklist` - Enter IP Addresses for disable to request the API. You can write down each IP in a new line


## Creating Request Mapping Relations
- `URL Parameter (req)` - This parameter should be sent as "req" for using this mapping
- `Related Table` - Related table for mapping
- `Columns in Response` - Select database columns which you want to return in response to the API users. (ONLY this columns will be shown in response) Be careful not to select sensitive columns
- `Read Only` - If this option activated, requests will be accept only "read" and "read_all" parameters. For this table, "Create", "Update" and "Delete" won't work
- `Allow Requesting with Index Keys` - If this option activated, API users don't have to send "key" parameter while using "read". API requests can be performed directly with one of the index keys (i.e if indexes are "id" and "email", request may have only "id=2", doesn't need to define "key=id&id=2"). This option is useful when simplicity matters
- `Limit Response Results` - You can limit query response when "read_all" operates. Use "0" for no limits
- `Fetch Data Order` - Choose "ORDER BY" value for fetching data from database. First index key field of table will be selected (mostly "id")


## Usage of Logs Section
You can activate also hidden parameters for listing in **API Logs** Page. Of course, when you click any log, you can show more details about that request.

Parameters can be shown in listing:

- Captured Date
- Request Status
- Request Method
- IP Address
- Time Spent
- Used Key
- Is API Activated when Requested
- Request URL
- Browser Info
- Referer Info


## Adding IP, IP Block or IP Notations to Blacklist
- You can enter IP Addresses for disable to request the API. Write down each IP in a new line
- You can use following rules for IP ranges to block:
    - Direct IP: 1.2.3.4
    - Wildcard format: 1.2.3.*
    - CIDR format: 1.2.3/24 or 1.2.3.4/255.255.255.0
    - Start-End IP format: 1.2.3.0-1.2.3.255

Matching Examples:

Rule | Status | IP for Matching
------------- | ------------- | -------------
80.140.*.* | rule will match IP | 80.140.2.2
`80.140.*.*` | `rule will NOT match IP` | `80.141.2.2`
80.140/16 | rule will match IP | 80.140.2.3
1.2.3.0-1.2.255.255 | rule will match IP | 1.2.3.4
`80.140.0.0-80.140.255.255` | `rule will NOT match IP` | `90.35.6.12`
80.76.201.32/27 | rule will match IP | 80.76.201.37
`80.76.201.32/27` | `rule will NOT match IP` | `81.76.201.37`
80.76.201.32/255.255.255.224 | rule will match IP | 80.76.201.38
80.76.201.32/255.255.255.* | rule will match IP | 80.76.201.39
`80.76.201.64/27` | `rule will NOT match IP` | `80.76.201.40`
`192.168.3.0/24` | `rule will NOT match IP` | `192.168.1.42`
127.0.0.0-129.0.0.0 | rule will match IP | 128.0.0.0


## GET Query samples for API
Method | Name | Query
------------- | ------------- | -------------
GET | Fetch one result | ?auth=`write_api_admin_key`&do=`read`&req=`users`&id=`1`&type=`xml`
GET | Fetch all results | ?auth=`write_api_admin_key`&do=`read_all`&req=`users`&type=`xml`
GET | Create new entry | ?auth=`write_api_admin_key`&do=`create`&req=`users`&name=`John`&email=`john@doe.com`&ip=`127.0.0.1`&type=`json`
GET | Update an existing entry | ?auth=`write_api_admin_key`&do=`update`&req=`users`&key=`id`&id=`1`&name=`Jane`&type=`json`


## POST Query samples for API with cURL
### cURL - Fetch one result
```php
$getOne =  http_build_query([
    // mandatory fields
    'auth'  => 'write_api_admin_key',
    'do'    => 'read',
    'req'   => 'users',
    'key'   => 'email',
    'email' => 'test@test.com',
    // optional fields
    'type'  => 'xml'
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://demo.nitm.net/api/');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $getOne);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$output = curl_exec($ch);

curl_close($ch);
dump($output);
```

### cURL - Fetch all results
```php
$getAll =  http_build_query([
    // mandatory fields
    'auth'  => 'write_api_admin_key',
    'do'    => 'read_all',
    'req'   => 'users',
    // optional fields
    'type'  => 'xml'
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://demo.nitm.net/api/');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $getAll);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$output = curl_exec($ch);

curl_close($ch);
dump($output);
```

### cURL - Create new entry
```php
$create =  http_build_query([
    // mandatory fields
    'auth'  => 'write_api_admin_key',
    'do'    => 'create',
    'req'   => 'users',
    // optional fields
    'ip'    => '',
    'name'  => '',
    'email' => '',
    'pass'  => '',
    'type'  => 'xml'
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://demo.nitm.net/api/');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $create);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$output = curl_exec($ch);

curl_close($ch);
dump($output);
```

### cURL - Update an existing entry
```php
$update =  http_build_query([
    // mandatory fields
    'auth'  => 'write_api_admin_key',
    'do'    => 'update',
    'req'   => 'users',
    'key'   => 'id',
    'id'    => 4,
    // optional fields
    'ip'    => '',
    'name'  => '',
    'email' => '',
    'pass'  => '',
    'type'  => 'xml'
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://demo.nitm.net/api/');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $update);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$output = curl_exec($ch);

curl_close($ch);
dump($output);
```


## POST Query samples for API with cURL
### cURL - Fetch one result
```bash
curl -i \
	--request POST 'https://demo.nitm.net/api/' \
	--data 'auth=write_api_admin_key' \
	--data 'do=read' \
	--data 'req=users' \
	--data 'key=email' \
	--data 'email=test@test.com' \
	--data 'type=xml'
```

### cURL - Fetch all results
```bash
curl -i \
	--request POST 'https://demo.nitm.net/api/' \
	--data 'auth=write_api_admin_key' \
	--data 'do=read_all' \
	--data 'req=users' \
	--data 'type=xml'
```

### cURL - Create new entry
```bash
curl -i \
	--request POST 'https://demo.nitm.net/api/' \
	--data 'auth=write_api_admin_key' \
	--data 'do=create' \
	--data 'req=users' \
	--data 'name=John' \
	--data 'email=john@doe.com' \
	--data 'ip=127.0.0.1' \
	--data 'type=xml'
```

### cURL - Update an existing entry
```bash
curl -i \
	--request POST 'https://demo.nitm.net/api/' \
	--data 'auth=write_api_admin_key' \
	--data 'do=update' \
	--data 'req=users' \
	--data 'key=id' \
	--data 'id=1' \
	--data 'type=xml'
```
