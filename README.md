# MoodMatch api

All calls are POST. <br>
url = [http://wfcbosch-nl.stackstaging.com/MoodMatch/index.php/](http://wfcbosch-nl.stackstaging.com/MoodMatch/index.php/)

### Headers
`Content-Type: application/json`

<br>

## New User
Send
```json
{
	"action": "newUser",
	"device_id": "testing2"
}
```

Return
```json
{
  "status": "ok",
  "matcher_uuid": "2aef1a52-bd44-4d23-8679-0e10f1adbbbe"
}
```

<br>

## Update Device ID
Send
```json
{
	"action": "updateDeviceId",
	"device_id": "updatedThrice",
	"matcher_uuid": "30c4b61c-b9d3-40d6-8c95-f0097f4b6e9c"
}
```

Return
```json
{
  "status": "ok"
}
```

<br>

## Create Match
Send
```json
{
	"action": "createMatch",
	"partner_uuid": "c672d2a3-4365-4dfb-ad70-95618eb2571c",
	"matcher_uuid": "30c4b61c-b9d3-40d6-8c95-f0097f4b6e9c"
}
```

Return
```json
{
  "status": "ok",
  "match": 1
}
```

<br>

## Change Partner
Send
```json
{
	"action": "changePartner",
	"partner_uuid": "30csadf a4b61c-b9d3-40d6-8c95-f0097f4b6e9c",
	"matcher_uuid": "9c7aa3a1-a5dc-4cea-8ffd-abcf235913b8"
}
```

Return
```json
{
  "status": "ok",
  "match": 11
}
```

<br>

## Current Status
Send
```json
{
	"action": "currentStatus",
	"match_id": 8,
	"matcher_uuid": "9c7aa3a1-a5dc-4cea-8ffd-abcf235913b8"
}
```

Return
```json
{
  "status": "ok",
  "you": 1,
  "partner": 1
}
```

<br>

## Reset Partner
Send
```json
{
	"action": "resetPartner",
	"matcher_uuid": "30c4b61c-b9d3-40d6-8c95-f0097f4b6eadsf9c"
}
```

Return
```json
{
  "status": "ok"
}
```

<br>

## Add Notification
Send
```json
{
	"action": "addNotification",
	"matcher_uuid": "f6f90be3-795a-4e81-8fc2-03235d3b9cdb",
	"match_id": 1,
	"mood": 1
}
```

Return
```json
{
  "status": "ok"
}
```

<br>

## History
Send
```json
{
	"action": "history",
	"matcher_uuid": "f6f90be3-795a-4e81-8fc2-03235d3b9cdb",
	"match_id": 1
}
```

Return
```json
{
  "status": "ok",
  "notifications": [
    {
      "id": 1,
      "user": "f6f90be3-795a-4e81-8fc2-03235d3b9cdb",
      "mood": 1,
      "date": "2020-12-30 20:46:55"
    },
    {
      "id": 2,
      "user": "9c7aa3a1-a5dc-4cea-8ffd-abcf235913b8",
      "mood": 1,
      "date": "2020-12-30 20:46:55"
    }
  ]
}
```

<br>

## Error structure

Return
```json
{
  "status": "nok",
  "error": "The partner you're trying to match with is already matched with someone else."
}
```
