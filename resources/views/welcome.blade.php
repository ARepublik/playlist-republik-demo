<!DOCTYPE html>
<html>
    <head>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
        <script src="https://js.stripe.com/v3/"></script>
    </head>
    <body>
        <div id="card"></div>
        <button id="checkout">Checkout</button>
        <script>
            var stripe = Stripe('{{$stripe_key}}');
            var elements = stripe.elements();
            var cardElement = elements.create('card');
            cardElement.mount('#card');
            $('#checkout').click(function () {
                stripe.confirmCardPayment('{{$payment_intent_secret}}', {
                    payment_method: {
                        card: cardElement
                    }
                }).then(function (result) {
                    console.log(result);
                })
            });
        </script>
    </body>
</html>
