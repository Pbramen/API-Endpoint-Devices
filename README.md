# Equipment API Endpoints
A RESTful API written in PHP that allows users/systems to create, search or update registered equipment. 
 
Entry to API endpoints starts in `api.php`.

> [!NOTE]
> This is a student project that utilizes a self-signed certificate. 
> If utilizing curl, use the -k option to suppress cert warnings.

## Standard JSON Response Format

On a successful HTTP request/response, a JSON payload will be returned in the following format: 

```JSON
{
    "Status": 200,
    "MSG": "Success",
    "Payload": {
        "Fields": {
            ...
        }
    },
    "Action" : "api/endpoint"
}
```
* "Status" - 200 code represents a successful response. 
* "MSG" - brief description of the response. 
* "Payload" - contains all data requested.
* "Fields" - contain all relevant data requested from MySQL database. For more information about specific endpoint responses, see each endpoints Return Section. 

* "Action" - link to next step, if applicable. "None Taken" is default if no additional step is needed.
## Search by equipment

Search by filter based on serial number, device and/or company.
* **URL**
    
    _/api/search_equip

* **Method** 

    `GET`

* **URL PARAMS**

   **OPTIONAL**

    &emsp; _serial number_

    &emsp;&emsp;`sn=[hexString]`

    &emsp;_device id_

    &emsp;&emsp;`d=[integer]`

    &emsp;_company_
    
    &emsp;&emsp;`c=[integer]`

    &emsp; _Limit_ - max number of returned records. Defaults to 0. 

    &emsp;&emsp;`limit=[integer >= 0 && integer <= 1000]`

    &emsp; _offset_ - used for pagination. Defaults to 0.

    &emsp;&emsp;`offset=[integer >= 0]`

    &emsp; _active_ - search based on status. 0 to include inactive and 1 for active only.

    &emsp;&emsp;`active=[0 or 1]`


* **SUCESSFUL RESPONSE**
    
    Returns Fields as an array of objects that contains all matching records. 
    ```JSON
    {
        "Status": 200,
        "MSG": "Success",
        "Payload": {
            "Fields": {                
                "sn": {
                "id": 4999839,
                "value": "32",
                "status": 1
                },
                "device": {
                    "id": 1,
                    "value": "vehicle",
                    "status": 1
                },
                "company": {
                    "id": 1,
                    "value": "Apple",
                    "status": 1
                },
                "r_id": 4999834
            } 
        },
        "Action": "None Taken"
    }
    ```
* **FAILED RESPONSES**
    
    No results found. 
    ```JSON
    {
        "MSG": "DNE"
    }
    ```

    Invalid active data type -> active is set, but not 0 or 1. 
    ``` json
    {
        "MSG": "Invalid active param"
    }
    ```

## Search one equipment
<p>Method: GET</p>

Given a serial number, returns a single equipment record. 
* **URL**
    
    _/api/search_one_equip_

* **Method** 

    `GET`

* **URL PARAMS**

    &emsp;Query by Serial Number:
    &emsp; _serial number_

    &emsp;Query by equipment id:
    &emsp; _serial number_
* **SUCESSFUL RESPONSE**
    
    On success, returns 200 status code and populates fields with a single record object. 

    ```JSON
    {
        "Status": 200,
        "MSG": "Success",
        "Payload": {
            "Fields": {                
                "sn": {
                    "id": 4999839,
                    "value": "32",
                    "status": 1
                },
                "device": {
                    "id": 1,
                    "value": "vehicle",
                    "status": 1
                },
                "company": {
                    "id": 1,
                    "value": "Apple",
                    "status": 1
                },
                "r_id": 4999834
            }  
        },
        "Action": "None Taken"
    }
    ```

## Search by device
<p>Method: GET</p>
Given a serial number, returns a single equipment record. 
* **URL**
    
    _/api/search_one_equip_

* **Method** 

    `GET`

* **URL PARAMS**

    **Requires at LEAST one of the following:**



    &emsp; _serial number_

* **SUCESSFUL RESPONSE**
    
    On success, returns 200 status code and populates fields with a single record object. 

    ```JSON
    {
        "Status": 200,
        "MSG": "Success",
        "Payload": {
            "Fields": {                
                "sn": {
                    "id": 4999839,
                    "value": "32",
                    "status": 1
                },
                "device": {
                    "id": 1,
                    "value": "vehicle",
                    "status": 1
                },
                "company": {
                    "id": 1,
                    "value": "Apple",
                    "status": 1
                },
                "r_id": 4999834
            }  
        },
        "Action": "None Taken"
    }
    ```


## Search by company
<p>Method: GET</p>

## Add equipment
<p>Method: POST</p>

## Add device
<p>Method: POST</p>

## Add company
<p>Method: POST</p>

## Update equipment
<p>Method: PUT</p>

## Update device
<p>Method: PUT</p>

## Update company
<p>Method: PUT</p>

## Other Response Errors

### Errors during validation/sanitation 

* Required <_param_> missing.

```JSON
{ "MSG": "Missing <param>" }
```

* Invalid encoding type. Populates Field with the detected encoding. 

```JSON
{ 
    "MSG": "Invalid character encoding",
    "Payload": {
        "Fields": {
            "encoding" "<encoding>"
        }
    }
}
```

* Invalid format for alpha only parameter.

```JSON
{ "MSG": "<param> should only have alpha characters." }
```

* Max character length for <_param_> exceeded.

```JSON
{ 
    "MSG": "Max Length Exceeded",
    "Payload": {
        "Fields": {
            "<param>": "<value>",
            "maxLength": 10, 
            "exceededLength": 15
        }
    }
}
```

