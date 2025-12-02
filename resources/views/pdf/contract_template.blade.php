<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vertrag zur Teilnahme</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 40px;
        }

        .container {
            max-width: 800px;
            margin: auto;
        }

        .header,
        .footer {
            text-align: center;
        }

        .content {
            margin-top: 20px;
        }

        .signature {
            margin-top: 40px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 8px;
            border: 1px solid #000;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="header">
            <img width="350" height="110" src="{{ $img }}" alt="">
            <h2>
                @if ($lang == 'de')
                    Vergleichsplattform Kompass Umzug
                @elseif ($lang == 'en')
                    Compass Moving Comparison Platform
                @elseif ($lang == 'fr')
                    Plateforme de comparaison Compass Déménagement
                @elseif ($lang == 'it')
                    Piattaforma di confronto Compass Traslochi
                @endif
            </h2>
            <h3>
                @if ($lang == 'de')
                    Vertrag zur Teilnahme an Anfragen
                @elseif ($lang == 'en')
                    Contract for Participation in Requests
                @elseif ($lang == 'fr')
                    Contrat de participation aux demandes
                @elseif ($lang == 'it')
                    Contratto di partecipazione alle richieste
                @endif
            </h3>
            <p>
                @if ($lang == 'de')
                    Datum:{{ $created_at }}
                @elseif ($lang == 'en')
                    Date: {{ $created_at }}
                @elseif ($lang == 'fr')
                    Date: {{ $created_at }}
                @elseif ($lang == 'it')
                    Data: {{ $created_at }}
                @endif
            </p>
        </div>

        <div class="content">
            <p><strong>
                    @if ($lang == 'de')
                        Zwischen:
                    @elseif ($lang == 'en')
                        Between:
                    @elseif ($lang == 'fr')
                        Entre :
                    @elseif ($lang == 'it')
                        Tra:
                    @endif
                </strong></p>
            <p><strong>Tsheck Gmbh</strong><br>
                Muristrasse 3<br>
                3123 Belp</p>

            <p><strong>
                    @if ($lang == 'de')
                        Und:
                    @elseif ($lang == 'en')
                        And:
                    @elseif ($lang == 'fr')
                        Et:
                    @elseif ($lang == 'it')
                        E:
                    @endif
                </strong></p>
            <table>
                <tr>
                    <td>
                        @if ($lang == 'de')
                            Firma
                        @elseif ($lang == 'en')
                            Company
                        @elseif ($lang == 'fr')
                            Entreprise
                        @elseif ($lang == 'it')
                            Azienda
                        @endif
                    </td>
                    <td>{{ $company_name ?? '__________' }}</td>
                </tr>
                <tr>
                    <td>
                        @if ($lang == 'de')
                            Kontaktperson
                        @elseif ($lang == 'en')
                            Contact Person
                        @elseif ($lang == 'fr')
                            Personne de contact
                        @elseif ($lang == 'it')
                            Persona di contatto
                        @endif
                    </td>
                    <td>{{ $contact_person ?? '__________' }}</td>
                </tr>
                <tr>
                    <td>
                        @if ($lang == 'de')
                            Adresse, PLZ
                        @elseif ($lang == 'en')
                            Address, ZIP Code
                        @elseif ($lang == 'fr')
                            Adresse, Code postal
                        @elseif ($lang == 'it')
                            Indirizzo, CAP
                        @endif
                    </td>
                    <td>{{ $address . ' , ' . $ZIPCode ?? '__________' }}</td>
                </tr>
                <tr>
                    <td>
                        @if ($lang == 'de')
                            Telefon-Nr.
                        @elseif ($lang == 'en')
                            Phone Number
                        @elseif ($lang == 'fr')
                            Téléphone
                        @elseif ($lang == 'it')
                            Numero di telefono
                        @endif
                    </td>
                    <td>{{ $phone ?? '__________' }}</td>
                </tr>
                <tr>
                    <td>
                        @if ($lang == 'de')
                            E-Mail
                        @elseif ($lang == 'en')
                            Email
                        @elseif ($lang == 'fr')
                            E-mail
                        @elseif ($lang == 'it')
                            E-mail
                        @endif
                    </td>
                    <td>{{ $email ?? '__________' }}</td>
                </tr>
                <tr>
                    <td>
                        @if ($lang == 'de')
                            Handy-Nr.
                        @elseif ($lang == 'en')
                            Mobile Number
                        @elseif ($lang == 'fr')
                            Portable
                        @elseif ($lang == 'it')
                            Numero di cellulare
                        @endif
                    </td>
                    <td>{{ $mobile ?? '__________' }}</td>
                </tr>
                <tr>
                    <td>
                        @if ($lang == 'de')
                            Homepage
                        @elseif ($lang == 'en')
                            Website
                        @elseif ($lang == 'fr')
                            Site Web
                        @elseif ($lang == 'it')
                            Sito Web
                        @endif
                    </td>
                    <td>{{ $website ?? '__________' }}</td>
                </tr>
                <tr>
                    <td>
                        @if ($lang == 'de')
                            Handelsregister-Nr.
                        @elseif ($lang == 'en')
                            Trade Register No.
                        @elseif ($lang == 'fr')
                            Numéro du registre du commerce
                        @elseif ($lang == 'it')
                            Numero del registro delle imprese
                        @endif
                    </td>
                    <td>{{ $trade_register ?? '__________' }}</td>
                </tr>

            </table>

            <h3>
                @if ($lang == 'de')
                    Teilnahmebedingungen
                @elseif ($lang == 'en')
                    Terms of Participation
                @elseif ($lang == 'fr')
                    Conditions de participation
                @elseif ($lang == 'it')
                    Termini di partecipazione
                @endif
            </h3>

            <p>
                @if ($lang == 'de')
                    Kundenanfragen, deren Kontaktdaten wie E-Mail oder Telefonnummer inkorrekt sind oder fehlen, sowie
                    doppelte Anfragen können innerhalb von 3 Tagen nach Erhalt zur Reklamation an uns über den
                    jeweiligen Link retournieret werden. Akzeptierte Reklamationen werden nicht verrechnet.
                    Firmenangaben, welche im internen Bereich des Portals angegeben werden müssen, der Wahrheit
                    entsprechen.
                @elseif ($lang == 'en')
                    Customer inquiries for which contact details such as email or telephone number are incorrect or
                    missing, as well as duplicate inquiries, can be returned to us for complaint via the respective link
                    within 3 days after receipt. Accepted complaints will not be charged. Company details, which must be
                    provided in the internal area of the portal, must correspond to the truth.
                @elseif ($lang == 'fr')
                    Les demandes de clients dont les coordonnées, telles que l'e-mail ou le numéro de téléphone, sont
                    incorrectes ou manquantes, ainsi que les demandes en double, peuvent être retournées pour
                    réclamation via le lien respectif dans les 3 jours suivant leur réception. Les réclamations
                    acceptées ne seront pas facturées. Les informations sur l'entreprise, qui doivent être fournies dans
                    la zone interne du portail, doivent être conformes à la réalité.
                @elseif ($lang == 'it')
                    Le richieste dei clienti, per le quali i dettagli di contatto come l'e-mail o il numero di telefono
                    sono errati o mancanti, così come le richieste duplicate, possono essere restituite a noi per
                    reclamo tramite il link corrispondente entro 3 giorni dalla ricezione. I reclami accettati non
                    saranno addebitati. Le informazioni aziendali, che devono essere fornite nell'area interna del
                    portale, devono corrispondere alla verità.
                @endif
            </p>

            <p>
                @if ($lang == 'de')
                    Der Vertrag kann von beiden Parteien jederzeit durch eine schriftliche Mitteilung gekündigt werden.
                    Es werden nur Anfragen, die bis zum Zeitpunkt der Kündigung vermittelt wurden, in Rechnung gestellt.
                    Über Anpassungen und Erweiterungen werden die Vertragspartner schriftlich informiert. Mit einer
                    schriftlichen Mitteilung können Sie die Anfragen auch pausieren und bei einem späteren Zeitpunkt
                    wieder freischalten.
                @elseif ($lang == 'en')
                    The contract can be terminated by either party at any time with written notice. Only inquiries that
                    were mediated up to the time of termination will be invoiced. The contracting parties will be
                    informed in writing of any adjustments and extensions. With written notice, you can also pause the
                    inquiries and reactivate them at a later time.
                @elseif ($lang == 'fr')
                    Le contrat peut être résilié par l'une ou l'autre des parties à tout moment par notification écrite.
                    Seules les demandes transmises jusqu'au moment de la résiliation seront facturées. Les partenaires
                    contractuels seront informés par écrit de tout ajustement ou extension. Par notification écrite,
                    vous pouvez également mettre en pause les demandes et les réactiver ultérieurement.
                @elseif ($lang == 'it')
                    Il contratto può essere risolto da entrambe le parti in qualsiasi momento con una comunicazione
                    scritta. Verranno fatturate solo le richieste mediate fino al momento della risoluzione. Le parti
                    contrattuali saranno informate per iscritto di eventuali adeguamenti ed estensioni. Con una
                    comunicazione scritta, è possibile anche mettere in pausa le richieste e riattivarle in un secondo
                    momento.
                @endif
            </p>

            <p>
                @if ($lang == 'de')
                    Die übermittelten Kontaktdaten dürfen nur im Zusammenhang mit der Kontaktaufnahme und
                    Offertenstellung verwendet werden. Die Kontaktdaten dürfen nicht geteilt oder weiterverkauft werden.
                    Bei einem Verdacht auf Verletzung des Vertrags wird der Vertrag nichtig und der Vertragspartner wird
                    gesperrt.
                @elseif ($lang == 'en')
                    The transmitted contact details may only be used for contacting and submitting offers. The contact
                    details may not be shared or resold. In case of suspected breach of contract, the contract will
                    become null and void and the contracting partner will be blocked.
                @elseif ($lang == 'fr')
                    Les coordonnées transmises ne doivent être utilisées que dans le cadre de la prise de contact et de
                    la soumission d'offres. Les coordonnées ne doivent pas être partagées ou revendues. En cas de
                    suspicion de violation du contrat, celui-ci sera annulé et le partenaire contractuel sera bloqué.
                @elseif ($lang == 'it')
                    I dettagli di contatto trasmessi possono essere utilizzati solo per contattare e inviare offerte. I
                    dettagli di contatto non devono essere condivisi o rivenduti. In caso di sospetta violazione del
                    contratto, il contratto sarà considerato nullo e il partner contrattuale sarà bloccato.
                @endif
            </p>

            <p>
                @if ($lang == 'de')
                    Der Vertragspartner erklärt mit diesem Vertrag, dass alle gesetzlichen Bestimmungen eingehalten
                    werden, auch in Bezug auf die Anstellung der Arbeiter und deren Sozialleistungen. <br> Beide
                    Parteien erklären sich mit dem Inhalt des Vertrages einverstanden.
                @elseif ($lang == 'en')
                    By signing this contract, the contracting partner declares that all legal requirements are met,
                    including those regarding the employment of workers and their social benefits. <br> Both parties
                    agree to the content of the contract.
                @elseif ($lang == 'fr')
                    Le partenaire contractuel déclare, par ce contrat, que toutes les dispositions légales sont
                    respectées, y compris en ce qui concerne l'emploi des travailleurs et leurs prestations sociales.
                    <br> Les deux parties acceptent le contenu du contrat.
                @elseif ($lang == 'it')
                    Il partner contrattuale dichiara con questo contratto che tutte le disposizioni legali sono
                    rispettate, inclusi gli obblighi relativi all'impiego dei lavoratori e ai loro benefici sociali.
                    <br> Entrambe le parti accettano il contenuto del contratto.
                @endif
            </p>

            <div class="signature">
                <p>
                    @if ($lang == 'de')
                        Ort, Datum: ___________
                    @elseif ($lang == 'en')
                        Location, Date: ___________
                    @elseif ($lang == 'fr')
                        Lieu, Date: ___________
                    @elseif ($lang == 'it')
                        Luogo, Data: ___________
                    @endif
                </p>
                <p>
                    @if ($lang == 'de')
                        Unterschrift ___________
                    @elseif ($lang == 'en')
                        Signature ___________
                    @elseif ($lang == 'fr')
                        Signature ___________
                    @elseif ($lang == 'it')
                        Firma ___________
                    @endif
                </p>
            </div>
        </div>
    </div>

</body>

</html>
