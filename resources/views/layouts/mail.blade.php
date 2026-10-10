<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>@yield('title') - {{ config('app.name') }} </title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400&display=swap" rel="stylesheet">
    <style type="text/css">
        body {font-family: 'Roboto', Calibri, Helvetica, sans-serif; font-size: 18px; font-weight: 300;}
        h1, .h1, h2, .h2, h3, .h3, h4, .h4, h5, .h5, h6, .h6 {
            margin: 0 0 0.3em 0;
            font-weight: 400;
            color: #000000;
        }
        h1, .h1 {font-size: 28px; }
        h2, .h2 {font-size: 24px; }
        h3, .h3 {font-size: 22px; }
        h4, .h4 {font-size: 20px; }
        h5, .h5 {font-size: 18px; }
        a {color: #007bff; text-decoration: none;}
        .btn {
            display: inline-block;
            border-radius: 5px;
            letter-spacing: 2px;
            text-transform: uppercase;
            transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            font-weight: 400;
        }
        .btn-primary {
            font-size: 16px;
            color: #ffffff;
            letter-spacing: 0;
            padding: 15px 20px;
            text-align: center;
            background: #278E74;
            box-shadow: 0 10px 20px 0 rgba(0, 0, 0, 0.14);
            border: none;
        }
        .text-small {font-size: small;}
        .text-center {text-align: center;}
        .text-bold {font-weight: bold;}
        .text-muted {color: #777777;}
        code {color: #e83e8c; word-wrap: break-word;}
        .content {margin: 20px 0;}
        .footer {font-size: small;}
    </style>
</head>
<body bgcolor="#ffffff" text="#000000" style="padding:15px; background-color:#ffffff;">
    <h1 class="header">
        {{ config('app.name') }}
    </h1>

    <div class="content">
        @yield('content')
    </div>

    <div class="footer">
        {{ __('Please do not reply to this email') }} <br>
        {{ __('If you need to contact us please email us at') }}
        <a href="mailto:{{ config('app.mail_support') }}">{{ config('app.mail_support') }}</a>
    </div>
</body>
</html>
