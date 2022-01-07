<!--[if mso | IE]>
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="600" align="center" style="width:600px;">
    <tr>
        <td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;bgcolor="#3853a3">
<![endif]-->
            @if ($backgroundSection->image)
            <div style="margin:0 auto;max-width:600px;background:#3853a3 url(cid:{{ $backgroundSection->image->file }}) center center/cover no-repeat">
                <!--[if mso | IE]>
                <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="width:600px">
                    <v:fill origin="0.5, 0" position="0.5,0" type="frame" size="100%,100%" src="cid:{{ $backgroundSection->image->file }}" color="#3853a3" />
                    <v:textbox style="mso-fit-shape-to-text:true" inset="0,0,0,0">
                <![endif]-->
                        <table role="presentation" cellpadding="0" cellspacing="0" style="font-size:0;width:100%;background:#3853a3 url(cid:{{ $backgroundSection->image->file }}) center center/cover no-repeat" align="center" border="0" background="cid:{{ $backgroundSection->image->file }}">
            @else
            <div style="margin:0 auto;max-width:600px;background:#3853a3">
                <table role="presentation" cellpadding="0" cellspacing="0" style="font-size:0;width:100%;background:#3853a3" align="center" border="0">
            @endif
                            <tbody>
                                <tr>
                                    <td style="text-align:center;vertical-align:top;direction:ltr;font-size:0;padding:20px 0;padding-bottom:0;padding-top:0">
                                        <!--[if mso | IE]>
                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="vertical-align:top;width:600px;">
                                        <![endif]-->
                                                    <div class="mj-column-per-100 outlook-group-fix" style="vertical-align:top;display:inline-block;direction:ltr;font-size:16px;text-align:left;width:100%">
                                                        <table role="presentation" cellpadding="0" cellspacing="0" style="vertical-align:top" width="100%" border="0">
                                                            <tbody>
                                                                <tr>
                                                                    <td style="word-wrap:break-word;font-size:0;padding:20px 25px;padding-top:20px;padding-bottom:20px;padding-right:25px;padding-left:25px" align="center">
                                                                        <div class="newsletter-text fallback-font" style="cursor:auto;color:#000;font-family:'Museo Sans',Roboto,Arial,Verdana,sans-serif;font-size:16px;line-height:1.2;text-align:center">
                                                                            @if ($backgroundSection->title)
                                                                                <p><span style="font-size:32px"><a href="{{ Helper::route() }}" style="text-decoration:none;color:inherit"><span style="font-weight:700"><span style="color:#fff;font-family:'Museo Sans', Roboto, Arial, Verdana, sans-serif;line-height:1.2;">{{ $backgroundSection->title }}</span></span></a></span></p>
                                                                            @endif

                                                                            @if ($backgroundSection->description)
                                                                                <p style="line-height:1.5;"><span style="font-size:20px"><span style="color:#fff;font-family:'Museo Sans', Roboto, Arial, Verdana, sans-serif;">{!! $backgroundSection->description !!}</span></span></p>
                                                                            @endif
                                                                        </div>
                                                                    </td>
                                                                </tr>

                                                                @if ($backgroundSection->button_text)
                                                                    <tr>
                                                                        <td style="word-wrap:break-word;font-size:0;padding:20px 0;padding-top:50px;padding-bottom:20px;padding-right:0;padding-left:0" align="center">
                                                                            <table role="presentation" cellpadding="0" cellspacing="0" style="border-collapse:separate" align="center" border="0">
                                                                                <tbody>
                                                                                    <tr>
                                                                                        <td style="border:none;border-radius:3px;color:#3853a3;cursor:auto;padding:10px 25px" align="center" valign="middle" bgcolor="#ffffff"><a href="{{ $backgroundSection->button_link }}" class="fallback-font" style="text-decoration:none;background:#ffffff;color:#3853a3;font-family:'Museo Sans',Roboto,Arial,Verdana,sans-serif;font-size:18px;font-weight:400;line-height:1.5;text-transform:none;margin:0" target="_blank">{{ $backgroundSection->button_text }}</a></td>
                                                                                    </tr>
                                                                              </tbody>
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                @endif

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
                @if ($backgroundSection->image)
                <!--[if mso | IE]>
                        <p style="margin:0;mso-hide:all"><o:p xmlns:o="urn:schemas-microsoft-com:office:office"> </o:p></p>
                    </v:textbox>
                </v:rect>
                <![endif]-->
                @endif
            </div>
<!--[if mso | IE]>
        </td>
    </tr>
</table>
<![endif]-->
