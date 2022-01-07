@inject('carbon', '\Carbon\Carbon')

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;margin-top:0 !important;margin-bottom:0 !important;margin-right:auto !important;margin-left:auto !important;padding-top:0 !important;padding-bottom:0 !important;padding-right:0 !important;padding-left:0 !important;height:100% !important;width:100% !important;">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="x-apple-disable-message-reformatting" />
    <title>{{ $subject }}</title>
    <style type="text/css">
    html,
    body {
        margin: 0 auto !important;
        padding: 0 !important;
        height: 100% !important;
        width: 100% !important
    }

    #MessageViewBody a {
        color: inherit;
        text-decoration: underline;
        text-decoration-color: #ffcf40;
        font-size: inherit;
        font-family: inherit;
        font-weight: inherit;
        line-height: inherit
    }

    * {
        -ms-text-size-adjust: 100%;
        -webkit-text-size-adjust: 100%
    }

    div[style*="margin: 16px 0"] {
        margin: 0 !important
    }

    table,
    td {
        mso-table-lspace: 0pt !important;
        mso-table-rspace: 0pt !important
    }

    table {
        border-spacing: 0 !important;
        border-collapse: separate;
         !important;
        table-layout: fixed !important;
        margin: 0 auto !important
    }

    table table table {
        table-layout: fixed
    }

    *[x-apple-data-detectors],
    .x-gmail-data-detectors,
    .x-gmail-data-detectors * {
        border-bottom: 0 !important;
        cursor: default !important;
        color: inherit !important;
        text-decoration: none !important;
        font-size: inherit !important;
        font-family: inherit !important;
        font-weight: inherit !important;
        line-height: inherit !important
    }

    li {
        padding-bottom: 5px !important;
    }

    a {
        color: #333333;
        text-decoration: underline !important;
        text-decoration-color: #ffcf40 !important;
        border-bottom: 2px solid #ffcf40
    }

    p {
        margin-bottom: 10px !important;
        padding-top: 7px;
        padding-bottom: 7px;
    }

    .content {
        font-family: helvetica, 'lucida grande', 'lucida sans unicode', 'lucida sans', verdana, sans-serif;
        color: #333333;
        padding: 5px 15px 5px 15px;
        text-align: left;
        line-height: 23px !important;
        font-size: 16px !important
    }

    .separator {
        padding: 0px !important;
        font-size: 5px;
    }

    .border-sides {
        border-left-style: solid;
        border-right-style: solid;
        border-color: #eeeeee;
        border-left-width: 1px;
        border-right-width: 2px;
    }

    .border-bottom {
        border-left-style: solid !important;
        border-right-style: solid;
        border-color: #eeeeee;
        border-left-width: 1px;
        border-right-width: 2px;
        border-bottom-style: solid;
        border-bottom-width: 2px;
        border-bottom-right-radius: 20px;
        border-bottom-left-radius: 20px;
    }

    .top-rounded {
        border-top-style: solid;
        border-color: #eeeeee;
        border-top-width: 1px;
        border-top-right-radius: 20px;
    }

    .tags-new {
        background-color: #ffcf40 !important;
        background-clip: padding-box;
        font-family: helvetica, sans-serif;
        text-align: center;
        color: #fff;
        font-size: 14px;
        line-height: 20px;
        padding-left: 9px;
        padding-right: 9px
    }

    .tags {
        background-color: #363636 !important;
        background-clip: padding-box;
        font-family: helvetica, sans-serif;
        text-align: center;
        color: #fff;
        font-size: 14px;
        line-height: 20px;
        padding-left: 9px;
        padding-right: 9px
    }

    .btn-primary {
        background-color: #ffcf40 !important;
        background-clip: padding-box;
        font-family: helvetica, sans-serif;
        text-align: center;
        color: #fff;
        font-size: 14px;
        line-height: 20px;
        font-weight: bold;
        padding-left: 9px;
        padding-right: 9px
    }

    @media only screen and (max-width: 480px) {
        a[href^="tel"],
        a[href^="sms"] {
            text-decoration: none;
            color: #fff;
            cursor: default;
            font-size: 16px
        }

        .tags-new,
        .tags {
            font-size: 14px !important;
            line-height: 16px !important;
            padding-left: 4px;
            padding-right: 4px
        }

        .btn-primary {
            font-size: 14px !important;
            line-height: 16px !important;
            padding-left: 4px;
            padding-right: 4px
        }

        .content {
            padding: 5px 15px 5px 15px !important;
            text-align: left;
            line-height: 18px !important;
            font-size: 14px !important
        }

        html {
            font-size: 16px;
            line-height: 24px
        }

        .devicewidth {
            width: 100% !important;
            padding: 0px 0px !important
        }
    }

    @media only screen and (max-width:408px) {
        .tags-new,
        .tags {
            font-size: 14px;
            line-height: 16px;
            padding-left: 4px;
            padding-right: 4px
        }

        .btn-primary {
            font-size: 14px;
            line-height: 16px;
            padding-left: 4px;
            padding-right: 4px
        }

        .content {
            padding: 5px 15px 5px 15px !important;
            text-align: left;
            line-height: 17px !important;
            font-size: 14px !important
        }
    }
    </style>
</head>

<body bgcolor="#ffffff" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;margin-top:0 !important;margin-bottom:0 !important;margin-right:auto !important;margin-left:auto !important;padding-top:0 !important;padding-bottom:0 !important;padding-right:0 !important;padding-left:0 !important;height:100% !important;width:100% !important;">
    <div class="email-container" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;max-width:666px;margin-top:auto;margin-bottom:auto;margin-right:auto;margin-left:auto;">
        <table width="666" bgcolor="#ffffff" cellpadding="0" cellspacing="0" border="0" class="devicewidth" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;mso-table-lspace:0pt !important;mso-table-rspace:0pt !important;border-spacing:0 !important;border-collapse:separate;table-layout:fixed !important;margin-top:0 !important;margin-bottom:0 !important;margin-right:auto !important;margin-left:auto !important;">
            <tbody style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">
                <tr style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">
                    <td style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;padding-left:0px;padding-top:10px;mso-table-lspace:0pt !important;mso-table-rspace:0pt !important;">
                        <table height="24" align="left" valign="middle" border="0" cellpadding="0" cellspacing="0" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;padding-left:0px;mso-table-lspace:0pt !important;mso-table-rspace:0pt !important;border-spacing:0 !important;border-collapse:separate;table-layout:fixed !important;margin-top:0 !important;margin-bottom:0 !important;margin-right:auto !important;margin-left:auto !important;">
                            <tbody style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">
                                <tr style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">
                                    <td align="center" valign="middle" height="28" class="tags" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;color:#ffffff;border-radius:5px 5px 0px 0px;mso-table-lspace:0pt !important;mso-table-rspace:0pt !important;background-color:#363636 !important;background-clip:padding-box;font-family:helvetica, sans-serif;text-align:center;font-size:14px;line-height:20px;padding-left:9px;padding-right:9px;">{{ $carbon->format('Y-m-d') . ' | ' . trans('newsletters.' . $template . '.name') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">
                    <td class="separator top-rounded border-sides" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;mso-table-lspace:0pt !important;mso-table-rspace:0pt !important;padding-top:0px !important;padding-bottom:0px !important;padding-right:0px !important;padding-left:0px !important;font-size:5px;border-left-style:solid;border-right-style:solid;border-left-width:1px;border-right-width:2px;border-top-style:solid;border-color:#eeeeee;border-top-width:1px;border-top-right-radius:20px;">
                        <br style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">
                    </td>
                </tr>
                <tr style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">
                    <td width="100%" height="10" bgcolor="#ffffff" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;mso-table-lspace:0pt !important;mso-table-rspace:0pt !important;border-left-style:solid;border-right-style:solid;border-color:#eeeeee;border-left-width:1px;border-right-width:2px;"></td>
                </tr>
                <tr style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">
                    <td align="left" class="content border-sides" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;mso-table-lspace:0pt !important;mso-table-rspace:0pt !important;font-family:helvetica, 'lucida grande', 'lucida sans unicode', 'lucida sans', verdana, sans-serif;color:#333333;padding-top:5px;padding-bottom:5px;padding-right:15px;padding-left:15px;text-align:left;line-height:23px !important;font-size:16px !important;border-left-style:solid;border-right-style:solid;border-color:#eeeeee;border-left-width:1px;border-right-width:2px;">
                        {!! $body !!}
                    </td>
                </tr>
                <tr style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">
                    <td align="center" class="content border-sides" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;mso-table-lspace:0pt !important;mso-table-rspace:0pt !important;font-family:helvetica, 'lucida grande', 'lucida sans unicode', 'lucida sans', verdana, sans-serif;color:#333333;padding-top:5px;padding-bottom:5px;padding-right:15px;padding-left:15px;text-align:left;line-height:23px !important;font-size:16px !important;border-left-style:solid;border-right-style:solid;border-color:#eeeeee;border-left-width:1px;border-right-width:2px;">
                        <table align="center" valign="middle" width="90%" border="0" cellpadding="0" cellspacing="0" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;padding-left:0px;mso-table-lspace:0pt!important;mso-table-rspace:0pt!important;border-spacing:0!important;border-collapse:collapse;table-layout:fixed!important;margin-top:0!important;margin-bottom:0!important;margin-right:auto!important;margin-left:auto!important;width:100%"><tr style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;"><td align="center" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;mso-table-lspace:0pt!important;mso-table-rspace:0pt!important;border-style:solid;border-color:#eeeeee;border-width:1px 0 0 0;border-bottom-width:0;background:none;height:1px;width:100%;margin:0px;padding-top:0px;padding-bottom:0px;"></td></tr></table>
                    </td>
                </tr>
                <tr style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">
                    <td align="center" class="content border-sides" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;mso-table-lspace:0pt !important;mso-table-rspace:0pt !important;font-family:helvetica, 'lucida grande', 'lucida sans unicode', 'lucida sans', verdana, sans-serif;color:#333333;padding-top:5px;padding-bottom:5px;padding-right:15px;padding-left:15px;text-align:left;line-height:23px !important;font-size:16px !important;border-left-style:solid;border-right-style:solid;border-color:#eeeeee;border-left-width:1px;border-right-width:2px;">
                        <p style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;margin-top:0px !important;margin-bottom:0px !important;margin-right:0px !important;margin-left:0px !important;padding-top:7px;padding-bottom:7px;">Kind Regards,</p>
                    </td>
                </tr>
                <tr style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">
                    <td align="center" class="content border-sides" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;mso-table-lspace:0pt !important;mso-table-rspace:0pt !important;font-family:helvetica, 'lucida grande', 'lucida sans unicode', 'lucida sans', verdana, sans-serif;color:#333333;padding-top:5px;padding-bottom:5px;padding-right:15px;padding-left:15px;text-align:left;line-height:23px !important;font-size:16px !important;border-left-style:solid;border-right-style:solid;border-color:#eeeeee;border-left-width:1px;border-right-width:2px;">
                        <a href="https://www.mespil.ie/?utm_source=email&utm_medium=email&utm_campaign=signature&utm_content=logo" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;text-decoration:none!important;"><img width="250" border="0" alt="@lang('newsletters.www-mespil-ie.logo')" src="cid:www-mespil-ie-logo.png" class="imgpop-header" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;display:block;border-style:none;outline-style:none;text-decoration:none;-ms-interpolation-mode:bicubic;padding-top:5px;padding-bottom:15px;padding-left:0px;padding-right:0px;" /></a>
                    </td>
                </tr>
                <tr style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">
                    <td align="left" class="content border-sides" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;mso-table-lspace:0pt !important;mso-table-rspace:0pt !important;font-family:helvetica, 'lucida grande', 'lucida sans unicode', 'lucida sans', verdana, sans-serif;color:#333333;padding-top:5px;padding-bottom:5px;padding-right:15px;padding-left:15px;text-align:left;line-height:23px !important;font-size:16px !important;border-left-style:solid;border-right-style:solid;border-color:#eeeeee;border-left-width:1px;border-right-width:2px;">
                        <table width="100%" align="left" valign="middle" border="0" cellpadding="0" cellspacing="0" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;padding-left:0px;mso-table-lspace:0pt !important;mso-table-rspace:0pt !important;border-spacing:0 !important;border-collapse:separate;table-layout:fixed !important;margin-top:0 !important;margin-bottom:0 !important;margin-right:auto !important;margin-left:auto !important;">
                            <tbody style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">
                                <tr style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">
                                    <td width="300" align="left" valign="middle" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;mso-table-lspace:0pt !important;mso-table-rspace:0pt !important;padding-top:0 !important;padding-bottom:0 !important;padding-right:0 !important;padding-left:0 !important;line-height:20px !important;font-size:14px !important;">
                                        <p style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;margin-top:0px !important;margin-bottom:0px !important;margin-right:0px !important;margin-left:0px !important;padding-top:7px;padding-bottom:7px;line-height:20px !important;font-size:14px !important;">Suite 1, The Eden Gate Centre, Priory Road, Delgany, Co Wicklow, A63 D903, Ireland</p>
                                    </td>
                                    <td>&nbsp;</td>
                                 </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">
                    <td align="left" class="content border-sides" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;mso-table-lspace:0pt !important;mso-table-rspace:0pt !important;font-family:helvetica, 'lucida grande', 'lucida sans unicode', 'lucida sans', verdana, sans-serif;color:#333333;padding-top:5px;padding-bottom:5px;padding-right:15px;padding-left:15px;text-align:left;line-height:20px !important;font-size:14px !important;border-left-style:solid;border-right-style:solid;border-color:#eeeeee;border-left-width:1px;border-right-width:2px;">
                        <p style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;margin-top:0px !important;margin-bottom:0px !important;margin-right:0px !important;margin-left:0px !important;padding-top:7px;padding-bottom:7px;line-height:20px !important;font-size:14px !important;"><a href="tel:+35316697670" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;color:#333333;text-decoration:none!important;border-bottom-width:2px;border-bottom-style:solid;border-bottom-color:#ffcf40;line-height:20px !important;font-size:14px !important;">+353 1 669 7670</a>&nbsp;<span style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;color: #aaaaaa;line-height:20px !important;font-size:18px !important;display:inline-block;height:16px;vertical-align: middle;">|</span>&nbsp;<a href="mailto:info@mespil.ie" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;color:#333333;text-decoration:none!important;border-bottom-width:2px;border-bottom-style:solid;border-bottom-color:#ffcf40;line-height:20px !important;font-size:14px !important;">info@mespil.ie</a>&nbsp;<span style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;color: #aaaaaa;line-height:20px !important;font-size:18px !important;display:inline-block;height:16px;vertical-align: middle;">|</span>&nbsp;<a href="https://www.mespil.ie?utm_source=email&utm_medium=email&utm_campaign=signature&utm_content=website" target="_blank" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;color:#333333;text-decoration:none!important;border-bottom-width:2px;border-bottom-style:solid;border-bottom-color:#ffcf40;line-height:20px !important;font-size:14px !important;">www.mespil.ie</a></p>
                    </td>
                </tr>
                <tr style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">
                    <td width="100%" align="left" class="content border-sides" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;padding-top:12px;padding-bottom:12px;mso-table-lspace:0pt !important;mso-table-rspace:0pt !important;font-family:helvetica, 'lucida grande', 'lucida sans unicode', 'lucida sans', verdana, sans-serif;color:#333333;padding-right:15px;padding-left:15px;text-align:left;line-height:23px !important;font-size:14px !important;border-left-style:solid;border-right-style:solid;border-color:#eeeeee;border-left-width:1px;border-right-width:2px;">
                        <table align="left" valign="middle" border="0" cellpadding="0" cellspacing="0" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;padding-left:0px;mso-table-lspace:0pt !important;mso-table-rspace:0pt !important;border-spacing:0 !important;border-collapse:separate;table-layout:fixed !important;margin-top:0 !important;margin-bottom:0 !important;margin-right:auto !important;margin-left:auto !important;">
                            <tbody style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">
                                <tr style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">
                                    <td align="center" valign="middle" class="btn-primary" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;background-color:#ffcf40;background-clip:border-box;font-size:14px;font-family:Helvetica, sans-serif;text-align:center;color:#333333;font-weight:bold;padding-left:5px;padding-right:5px;border-radius:5px 5px 5px 5px;line-height:20px;mso-table-lspace:0pt !important;mso-table-rspace:0pt !important;">
                                        <a href="https://www.portugal-golden-visa.pt/?utm_source=email&utm_medium=email&utm_campaign=signature&utm_content=golden-visa-link" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;outline-style:none;padding-top:10px;padding-bottom:10px;padding-right:5px;padding-left:5px;color:#333333;background-color:#ffcf40;border-width:2px;border-style:solid;border-color:#ffcf40;font-family:helvetica, sans-serif;font-size:14px;display:inline-block;line-height:20px;mso-line-height-rule:exactly;text-align:center;text-decoration:none!important;">PORTUGAL GOLDEN VISA</a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">
                    <td width="100%" align="left" class="content border-sides" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;padding-top:0;padding-bottom:12px;mso-table-lspace:0pt !important;mso-table-rspace:0pt !important;font-family:helvetica, 'lucida grande', 'lucida sans unicode', 'lucida sans', verdana, sans-serif;color:#333333;padding-right:15px;padding-left:15px;text-align:left;line-height:23px !important;font-size:16px !important;border-left-style:solid;border-right-style:solid;border-color:#eeeeee;border-left-width:1px;border-right-width:2px;">
                        <table align="left" valign="middle" border="0" cellpadding="0" cellspacing="0" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;padding-left:0px;mso-table-lspace:0pt !important;mso-table-rspace:0pt !important;border-spacing:0 !important;border-collapse:separate;table-layout:fixed !important;margin-top:0 !important;margin-bottom:0 !important;margin-right:auto !important;margin-left:auto !important;">
                            <tbody style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">
                                <tr style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">
                                    <td width="36" height="36" align="center" valign="middle" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;mso-table-lspace:0pt !important;mso-table-rspace:0pt !important;padding-top:4px !important;padding-bottom:4px !important;padding-right:4px !important;padding-left:4px !important;background-color:#3b5998;border-radius:3px;">
                                        <a href="https://www.facebook.com/mespil.ie/" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;color:#333333;text-decoration:none!important;"><img width="20" border="0" alt="facebook" src="cid:facebook.png" class="image" height="20" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;display:block;border-style:none;outline-style:none;text-decoration:none;-ms-interpolation-mode:bicubic;vertical-align:middle;" /></a>
                                    </td>
                                    <td width="5" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;mso-table-lspace:0pt !important;mso-table-rspace:0pt !important;"></td>
                                    <td width="36" height="36" align="center" valign="middle" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;mso-table-lspace:0pt !important;mso-table-rspace:0pt !important;padding-top:4px !important;padding-bottom:4px !important;padding-right:4px !important;padding-left:4px !important;background-color:#0077b5;border-radius:3px;">
                                        <a href="https://www.linkedin.com/company/mespil/" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;color:#333333;text-decoration:none!important;"><img width="20" border="0" alt="linkedin" src="cid:linkedin.png" class="image" height="20" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;display:block;border-style:none;outline-style:none;text-decoration:none;-ms-interpolation-mode:bicubic;vertical-align:middle;" /></a>
                                    </td>
                                    <td width="5" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;mso-table-lspace:0pt !important;mso-table-rspace:0pt !important;"></td>
                                    <td width="36" height="36" align="center" valign="middle" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;mso-table-lspace:0pt !important;mso-table-rspace:0pt !important;padding-top:4px !important;padding-bottom:4px !important;padding-right:4px !important;padding-left:4px !important;background-color:#ff0000;border-radius:3px;">
                                        <a href="https://www.youtube.com/channel/UCeHen6ED-M6KECL9LEYTnOQ" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;color:#333333;text-decoration:none!important;"><img width="20" border="0" alt="youtube" src="cid:youtube.png" class="image" height="20" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;display:block;border-style:none;outline-style:none;text-decoration:none;-ms-interpolation-mode:bicubic;vertical-align:middle;" /></a>
                                    </td>
                                    <td width="5" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;mso-table-lspace:0pt !important;mso-table-rspace:0pt !important;"></td>
                                    <td width="36" height="36" align="center" valign="middle" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;mso-table-lspace:0pt !important;mso-table-rspace:0pt !important;padding-top:4px !important;padding-bottom:4px !important;padding-right:4px !important;padding-left:4px !important;background-color:#1da1f2;border-radius:3px;">
                                        <a href="https://twitter.com/MESPILIreland" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;color:#333333;text-decoration:none!important;"><img width="20" border="0" alt="twitter" src="cid:twitter.png" class="image" height="20" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;display:block;border-style:none;outline-style:none;text-decoration:none;-ms-interpolation-mode:bicubic;vertical-align:middle;" /></a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">
                    <td width="100%" class="separator border-bottom" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;mso-table-lspace:0pt !important;mso-table-rspace:0pt !important;padding-top:0px !important;padding-bottom:0px !important;padding-right:0px !important;padding-left:0px !important;font-size:5px;border-left-style:solid!important;border-right-style:solid;border-color:#eeeeee;border-left-width:1px;border-right-width:2px;border-bottom-style:solid;border-bottom-width:2px;border-bottom-right-radius:20px;border-bottom-left-radius:20px;">
                        <br style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">
                    </td>
                </tr>
                <tr style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">
                    <td width="100%" height="20" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;mso-table-lspace:0pt !important;mso-table-rspace:0pt !important;"></td>
                </tr>

                @foreach ($messages as $message)
                    <tr style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">
                        <td style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;padding-left:0px;padding-top:10px;mso-table-lspace:0pt !important;mso-table-rspace:0pt !important;">
                            <table height="24" align="left" valign="middle" border="0" cellpadding="0" cellspacing="0" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;padding-left:0px;mso-table-lspace:0pt !important;mso-table-rspace:0pt !important;border-spacing:0 !important;border-collapse:separate;table-layout:fixed !important;margin-top:0 !important;margin-bottom:0 !important;margin-right:auto !important;margin-left:auto !important;">
                                <tbody style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">
                                    <tr style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">
                                        <td align="center" valign="middle" height="28" class="{{ $message->type == 'new' ? 'tags-new' : 'tags' }}" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;color:#{{ $message->type == 'new' ? '333333' : 'ffffff' }};border-radius:5px 5px 0px 0px;mso-table-lspace:0pt !important;mso-table-rspace:0pt !important;background-color:#{{ $message->type == 'new' ? 'ffcf40' : '363636' }} !important;background-clip:padding-box;font-family:helvetica, sans-serif;text-align:center;font-size:14px;line-height:20px;padding-left:9px;padding-right:9px;">{{ $carbon->parse($message->getOriginal('created_at'))->format('Y-m-d') . ' | ' . ($message->type == 'new' ? ($api->model->_parent->name ?: 'Inquiry') : trans('newsletters.' . $template . '.name')) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    <tr style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">
                        <td width="100%" height="2" class="separator top-rounded border-sides" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;mso-table-lspace:0pt !important;mso-table-rspace:0pt !important;padding-top:0px !important;padding-bottom:0px !important;padding-right:0px !important;padding-left:0px !important;font-size:5px;border-left-style:solid;border-right-style:solid;border-left-width:1px;border-right-width:2px;border-top-style:solid;border-color:#eeeeee;border-top-width:1px;border-top-right-radius:20px;">
                            <br style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">
                        </td>
                    </tr>
                    <tr style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">
                        <td align="center" class="content border-sides" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;mso-table-lspace:0pt !important;mso-table-rspace:0pt !important;font-family:helvetica, 'lucida grande', 'lucida sans unicode', 'lucida sans', verdana, sans-serif;color:#333333;padding-top:5px;padding-bottom:5px;padding-right:15px;padding-left:15px;text-align:left;line-height:23px !important;font-size:16px !important;border-left-style:solid;border-right-style:solid;border-color:#eeeeee;border-left-width:1px;border-right-width:2px;">
                            {!! $message->message !!}
                        </td>
                    </tr>
                    <tr style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">
                        <td width="100%" class="separator border-bottom" style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;mso-table-lspace:0pt !important;mso-table-rspace:0pt !important;padding-top:0px !important;padding-bottom:0px !important;padding-right:0px !important;padding-left:0px !important;font-size:5px;border-left-style:solid!important;border-right-style:solid;border-color:#eeeeee;border-left-width:1px;border-right-width:2px;border-bottom-style:solid;border-bottom-width:2px;border-bottom-right-radius:20px;border-bottom-left-radius:20px;">
                            <br style="-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">
                        </td>
                    </tr>
                @endforeach

            </tbody>
        </table>
    </div>
</body>
</html>
