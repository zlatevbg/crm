<!--[if mso | IE]>
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="600" align="center" style="width:600px;">
    <tr>
        <td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
<![endif]-->
            <div style="margin:0 auto;max-width:600px;">
                <table role="presentation" cellpadding="0" cellspacing="0" style="font-size:0;width:100%;" align="center" border="0">
                    <tbody>
                        <tr>
                            <td style="text-align:center;vertical-align:top;direction:ltr;font-size:0;padding:0 0;padding-bottom:0;padding-top:0">
                                <!--[if mso | IE]>
                                <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="vertical-align:top;width:600px;">
                                <![endif]-->
                                            <div class="mj-column-per-100 outlook-group-fix" style="vertical-align:top;display:inline-block;direction:ltr;font-size:16px;text-align:left;width:100%">
                                                <table role="presentation" cellpadding="0" cellspacing="0" style="vertical-align:top" width="100%" border="0">
                                                    <tbody>
                                                        @if ($textSection->title)
                                                            <tr>
                                                                <td style="word-wrap:break-word;font-size:16px;padding:25px 0;padding-top:25px;padding-bottom:25px;padding-right:0;padding-left:0">
                                                                    <div class="fallback-font" style="cursor:auto;color:#000;font-family:'Museo Sans',Roboto,Arial,Verdana,sans-serif;font-size:16px;line-height:1.2;">
                                                                        <h2><span style="color:#bc954d"><span style="font-weight:700"><span style="font-size:32px;font-family:'Museo Sans', Roboto, Arial, Verdana, sans-serif;line-height:1.2;">{{ $textSection->title }}</span></span></span></h2>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endif

                                                        @if ($textSection->content)
                                                            <tr>
                                                                <td style="word-wrap:break-word;font-size:16px;padding:10px 0;padding-top:10px;padding-bottom:10px;padding-right:0;padding-left:0">
                                                                    <div class="newsletter-text fallback-font" style="cursor:auto;color:#000;font-family:'Museo Sans',Roboto,Arial,Verdana,sans-serif;font-size:16px;line-height:1.5;">{!! $textSection->content !!}</div>
                                                                </td>
                                                            </tr>
                                                        @endif

                                                        @if ($textSection->button_text)
                                                            <tr>
                                                                <td style="word-wrap:break-word;font-size:16px;padding:15px 30px;padding-top:15px;padding-bottom:15px;padding-right:30px;padding-left:30px" align="center">
                                                                    <table role="presentation" cellpadding="0" cellspacing="0" style="border-collapse:separate" align="center" border="0">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td style="border:none;border-radius:3px;color:#fff;cursor:auto;padding:10px 25px" align="center" valign="middle" bgcolor="#bc954d"><a href="{{ $textSection->button_link }}" class="fallback-font" style="text-decoration:none;background:#bc954d;color:#fff;font-family:'Museo Sans',Roboto,Arial,Verdana,sans-serif;font-size:28px;font-weight:400;line-height:1.5;text-transform:none;margin:0" target="_blank">{{ $textSection->button_text }}</a></td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        @endif

                                                        @foreach ($textSection->images as $image)
                                                            <tr>
                                                                <td style="word-wrap:break-word;font-size:0;padding:10px 0;padding-top:10px;padding-bottom:10px;padding-right:0;padding-left:0" align="center">
                                                                    <table role="presentation" cellpadding="0" cellspacing="0" style="border-collapse:collapse;border-spacing:0" align="center" border="0">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td style="width:600px">
                                                                                    @if ($image->link)<a target="_blank" href="{{ $image->link }}">@endif
                                                                                    <img alt="" height="auto" src="cid:{{ $image->file }}" style="border:none;border-radius:0;display:block;font-size:16px;outline:0;text-decoration:none;width:100%;height:auto" width="600">
                                                                                    @if ($image->link)</a>@endif
                                                                                </td>
                                                                            </tr>
                                                                            @if ($image->name)
                                                                                <tr>
                                                                                    <td style="word-wrap:break-word;font-size:16px;padding:10px 25px;padding-top:10px;padding-bottom:10px;padding-right:25px;padding-left:25px" align="center">
                                                                                        <div class="fallback-font" style="cursor:auto;color:#000;font-family:'Museo Sans',Roboto,Arial,Verdana,sans-serif;font-size:16px;line-height:1.5;text-align:center">
                                                                                            <p><span style="color:#414141"><span style="font-size:12px"><span style="font-style:italic;font-family:'Museo Sans', Roboto, Arial, Verdana, sans-serif;">{{ $image->name }}</span></span></span></p>
                                                                                        </div>
                                                                                    </td>
                                                                                </tr>
                                                                            @endif
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                <!--[if mso | IE]>
                                        </td>
                                    </tr>
                                </table>
                                <![endif]-->
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
<!--[if mso | IE]>
        </td>
    </tr>
</table>
<![endif]-->
