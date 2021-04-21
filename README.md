# paypal-util
**Queste util sono solo delle linee guida!! il contenuto è soggetto a futuri refactoring e miglioramenti**

piccola util per **pagamenti semplici** con Paypal con redirect a Paypal

queste util permettono di gestire il pagamento con redirect al sito di paypal che, tramite appositi **redirect urls**
ritornerà sul vostro sito sia in caso di successo che di fallimento.

per utilizzare queste util è necessario installare l'sdk di Paypal in base alle versione delle api utilizzate:

- v1 --> "paypal/rest-api-sdk-php": "*",
- v2 --> "paypal/paypal-checkout-sdk": "1.0.1"


parametri necessari nei params per fare funzionare la util:

    CLIENT_ID  
    CLIENT_SECRET
    ENVIRONMENT -> indica l'ambiente in cui si trova l'applicativo al momento, accetta i valori "sandbox"(test) e "live"(produzione)
    

