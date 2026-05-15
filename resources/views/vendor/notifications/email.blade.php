<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>{{ config('app.name', 'SportaHub') }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #ffffff;
            color: #111827;
            font-family: Arial, Helvetica, sans-serif;
        }

        table {
            border-collapse: collapse;
        }

        a {
            color: inherit;
        }

        .email-wrapper {
            width: 100%;
            background: #ffffff;
            padding: 32px 16px;
        }

        .email-card {
            width: 100%;
            max-width: 560px;
            margin: 0 auto;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            overflow: hidden;
            background: #ffffff;
        }

        .email-content {
            padding: 40px;
        }

        .brand {
            margin: 0 0 28px;
            color: #2E4156;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: 0;
        }

        h1 {
            margin: 0;
            color: #111827;
            font-size: 30px;
            line-height: 1.2;
            font-weight: 800;
        }

        p {
            margin: 16px 0 0;
            color: #4b5563;
            font-size: 16px;
            line-height: 1.7;
        }

        .button-wrap {
            margin-top: 30px;
        }

        .button {
            display: inline-block;
            padding: 14px 22px;
            border-radius: 8px;
            color: #ffffff !important;
            background: #2E4156;
            font-size: 15px;
            font-weight: 700;
            text-decoration: none;
        }

        .footer-text {
            margin-top: 30px;
            color: #6b7280;
            font-size: 13px;
            line-height: 1.6;
        }

        .fallback {
            margin-top: 22px;
            color: #6b7280;
            font-size: 12px;
            line-height: 1.6;
            word-break: break-all;
        }

        @media (max-width: 600px) {
            .email-wrapper {
                padding: 18px 10px;
            }

            .email-content {
                padding: 28px 22px;
            }

            h1 {
                font-size: 25px;
            }

            .button {
                display: block;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <table role="presentation" class="email-wrapper" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <table role="presentation" class="email-card" width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="email-content">
                            <p class="brand">{{ $greeting ?? config('app.name', 'SportaHub') }}</p>

                            <h1>{{ $subject ?? config('app.name', 'SportaHub') }}</h1>

                            @foreach ($introLines as $line)
                                <p>{{ $line }}</p>
                            @endforeach

                            @isset($actionText)
                                <div class="button-wrap">
                                    <a href="{{ $actionUrl }}" class="button">
                                        {{ $actionText }}
                                    </a>
                                </div>

                                <p class="fallback">
                                    Ja poga nestrādā, atver šo saiti pārlūkā:<br>
                                    {{ $displayableActionUrl ?? $actionUrl }}
                                </p>
                            @endisset

                            @foreach ($outroLines as $line)
                                <p class="footer-text">{{ $line }}</p>
                            @endforeach

                            @isset($salutation)
                                <p class="footer-text">{{ $salutation }}</p>
                            @endisset
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
