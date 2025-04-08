<!DOCTYPE html>
<html>
    <head>
        <style>
            .bg{
                background: var(--primaryColor);
                width: 100vh;
                padding: 30px 20px;
            }
            .container{
                display: block !important;
                margin-inline: auto !important;
                border-radius: var(--roundedXLG);
                width: 50vh;
                min-width: 300px !important;
                height: auto;
                padding: 15px;
                background: #FFFFFF;
                box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;
                text-align: center;
                color: var(--darkColor);
            }
            h5{
                font-size:22px;
                margin: 0;
            }
            h6{
                font-size:14px;
                margin: 0;
                font-weight: 500 !important;
            }
        </style>
    </head>
    <body class="bg">
        <div class="container">
            <h5 style="margin-bottom:10px;">Hello there, {{$username}}</h5>

            <h6 style="color:#3b82f6;">You have successfully register your profile to Wardrobe, for final verification. Please input this <b>token</b> to token field in Register Page
            <h5>{{$token}}</h5>
        </div>
    </body>
</html>