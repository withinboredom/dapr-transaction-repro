# Transaction Repro issue

1. Init Dapr
```bash
dapr init
```

2. Start a dapr sidecar
```bash
dapr --app-id test --port 3500 run
```

3. Run a php server
```bash
php -S 0.0.0.0:3000
```

4. Visit http://localhost:3000
5. See the following output:

```
Setting some initial state: 
[
    {
        "key": "test",
        "value": "starting value"
    }
]

Retrieving sent state:
"starting value"

Sending a transaction that should fail with a bogus etag:
{
    "operations": [
        {
            "operation": "upsert",
            "request": {
                "key": "test",
                "value": "failed value",
                "etag": "3431231",
                "options": {
                    "concurrency": "first-write",
                    "consistency": "strong"
                }
            }
        },
        {
            "operation": "upsert",
            "request": {
                "key": "test",
                "value": "should not be set"
            }
        }
    ]
}

Got this result from the transaction:
{"errorCode":"ERR_STATE_TRANSACTION","message":"ERR Error running script (call to f_83e03ec05d6a3b6fb48483accf5e594597b6058f): @user_script:1: user_script:1: failed to set key test||test"}
But have this value stored:
"should not be set"
```
