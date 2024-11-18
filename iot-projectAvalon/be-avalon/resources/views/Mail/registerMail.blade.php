<!doctype html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }

        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border: 1px solid #dddddd;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0px 2px 6px rgba(0, 0, 0, 0.1);
        }

        .header {
            background-color: #0078f0;
            padding: 20px;
            text-align: center;
            color: white;
        }

        .header img {
            height: 40px;

            padding: 5px;
            border-radius: 4px;
        }

        .content {
            padding: 20px;
            text-align: center;
        }

        .content h1 {
            font-size: 20px;
            color: #333333;
            margin-bottom: 20px;
        }

        .content p {
            font-size: 16px;
            color: #555555;
            margin-bottom: 20px;
        }

        .confirmation-code {
            font-size: 24px;
            font-weight: bold;
            color: #0078f0;
            margin-bottom: 20px;
        }

        .footer {
            background-color: #f1f1f1;
            text-align: center;
            padding: 10px;
            font-size: 12px;
            color: #888888;
        }

        .footer a {
            color: #0078f0;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTFFohv-RSxhOkGrGLv5I700TpyleAXkpznSQ&s"
                alt="Jobster">
        </div>

        <!-- Content -->
        <div class="content">
            <h1>Hi {{ $name }},</h1>
            <p>Here's the confirmation code you requested:</p>
            <p class="confirmation-code">{{ $otp }}</p>
            <p style="color: #888888; font-size: 14px;">This code is valid for 5 minutes from now.</p>
            <p>If you didn't request this, you can ignore this email or let us know.</p>
            <p>Thanks,<br>The Jobster Team</p>
        </div>


        <!-- Footer -->
        <div class="footer">
            <p>© 2024 Jobster. All rights reserved.</p>
            <p><a href="#">Contact Us</a></p>
        </div>
    </div>
</body>

</html>
