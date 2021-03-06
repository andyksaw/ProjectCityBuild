<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="google-site-verification" content="Sp9E55tVkNph_ttvggLD52MY-ACeGfeivQbmWp7CWfo" />

        <title>Stylesheet</title>

        <link rel="stylesheet" type="text/css" href="{{ mix('assets/css/app.css') }}" />

        <script src="https://use.fontawesome.com/releases/v5.0.1/js/brands.js"></script>
        <script src="https://use.fontawesome.com/releases/v5.0.1/js/solid.js"></script>
        <!-- <script src="https://use.fontawesome.com/releases/v5.0.1/js/regular.js"></script> -->
        <script src="https://use.fontawesome.com/releases/v5.0.1/js/fontawesome.js"></script>

        <style>
            body {
                padding: 1em;
                background: #fff;
            }

            h1 {
                margin-top: 1em;
            }

            .card,
            .progressbar,
            .button,
            .bubble,
            .alert,
            .table {
                margin-bottom: 1em
            }

        </style>
    </head>
    <body>

        <div class="container">
            <h1>Overview of PCB's style</h1>
            <p>
                This time we're trying for a brighter, more toony style, where components look like blocks made of matte plastic.
                This page is merely a showcase of the components that, when combined, make up PCB's design.
            </p>

            <h1>Header 1</h1>
            <h2>Header 2</h2>
            <h3>Header 3</h3>
            <h4>Header 4</h4>
            <h5>Header 5</h5>
            <h6>Header 6</h6>

            <h1>Typography</h1>
            <p>
                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec posuere tincidunt felis, at rutrum tortor. Donec at cursus nulla. Curabitur tellus magna, consequat ut feugiat a, viverra ac tortor. Quisque sed pharetra orci. Nam a lorem quis magna mattis pretium. Curabitur consectetur nibh sed eros pulvinar, non mollis nulla feugiat. Sed fringilla dapibus mollis.
            </p>
            <p>
                Sed a urna tempus, pharetra ante sed, suscipit felis. Nulla condimentum consequat ante finibus cursus. Vestibulum sodales a felis vel eleifend. Nam dignissim, magna in lacinia venenatis, turpis sapien faucibus nulla, id euismod urna urna id turpis.
                <br />
                <br />
                Text with <strong>emphasis</strong>.<br />
                Text with <i>italics</i>.<br />
                Text with a <a href="#">link</a> to somewhere.<br />
                <small>Fine print text</small><br />
            </p>
            <p>
                <span class="primary">Primary text</span><br />
                <span class="accent">Accent text</span><br />
                <span class="light">Light text</span><br />
            </p>

            <h1>Dividers</h1>
            Option 1
            <hr class="divider" data-text="OR">
            Option 2

            <h1>Blockquotes</h1>
            <p>Used for quoting text.</p>
            <blockquote>
                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec posuere tincidunt felis, at rutrum tortor. Donec at cursus nulla.
            </blockquote>

            <h1>Speech Bubbles</h1>
            <p>Used for displaying user comments.</p>
            <div class="bubble left">
                <div class="bubble-avatar">
                    <img class="rounded" src="https://minotar.net/helm/_andy/32" width="32" />
                </div>
                <div class="bubble-comment">
                    <div class="bubble-text">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec posuere tincidunt felis, at rutrum tortor. Donec at cursus nulla.
                    </div>
                    <div class="bubble-date">
                        Sat, 16th of Dec, 2017 @ 9:31pm
                    </div>
                </div>
            </div>

            <div class="bubble right">
                <div class="bubble-comment">
                    <div class="bubble-text">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec posuere tincidunt felis, at rutrum tortor. Donec at cursus nulla.
                    </div>
                    <div class="bubble-date">
                        Sat, 16th of Dec, 2017 @ 9:31pm
                    </div>
                </div>
                <div class="bubble-avatar">
                    <img class="rounded" src="https://minotar.net/helm/_andy/32" width="32" />
                </div>
            </div>


            <h1>Badges</h1>
            <p>Used for displaying tags and counters.</p>
            <span class="badge">Basic</span>
            <span class="badge badge--primary">Primary</span>
            <span class="badge badge--secondary">Secondary</span>
            <span class="badge badge--accent">Accent</span>
            <p />
            Badges also work <span class="badge badge--secondary">inline</span> with text and even <span class="badge badge--primary"><i class="fas fa-edit"></i> with icons</span>.
            <p />
            Great for notification counters too:
            <div class="button button--secondary">
                <i class="fas fa-envelope"></i>
                Unread Posts <span class="badge badge--accent">2</span>
            </div>

            <h1>Cards</h1>
            <p>Used for grouping content.</p>
            <div class="card">
                <div class="card__body">
                    Basic (card)
                </div>
            </div>

            <div class="card card--primary">
                <div class="card__body">
                    Primary (card.primary)
                </div>
            </div>

            <div class="card card--secondary">
                 <div class="card__body">
                    Secondary (card.secondary)
                </div>
            </div>

            <div class="card card--accent">
                 <div class="card__body">
                    Accent (card.accent)
                </div>
            </div>

            <div class="card">
                <div class="card__header">
                    Header
                </div>
                <div class="card__body">
                    Basic (card)
                </div>
            </div>

            <div class="card card--primary">
                <div class="card__header">
                    Header
                </div>
                <div class="card__body">
                    Primary (card.primary)
                </div>
            </div>

            <div class="card card--secondary">
                <div class="card__header">
                    Header
                </div>
                <div class="card__body">
                    Secondary (card.secondary)
                </div>
            </div>

            <div class="card card--accent">
                <div class="card__header">
                    Header
                </div>
                <div class="card__body">
                    Accent (card.accent)
                </div>
            </div>

            <h1>Alerts</h1>
            <p>Used for flashing a quick message to the user, in particular showing errors/success when submitting a form.</p>

            <div class="alert alert--warning">
                <h3><i class="fas fa-exclamation-circle"></i> Warning</h3>
                <p>You have <a href="#">5 unchecked</a> player reports.</p>
            </div>
            <div class="alert alert--error">
                <h3><i class="fas fa-exclamation-circle"></i> Error</h3>
                <p>Image size exceeded the limit. Please upload an image less than <strong>1mb</strong>.</p>
            </div>
            <div class="alert alert--success">
                <h3><i class="fas fa-check"></i> Success</h3>
                <p>Ban appeal submitted succesfully!</p>
            </div>

            <h1>Progress Bars</h1>
            <div class="card card--primary">
                <div class="card__body">

                    <div class="progress progress--primary">
                        <div class="progress__bar">
                            <div class="progress__bar__fill" style="width:25%"></div>
                        </div>
                    </div>
                    
                    <div class="progress progress--secondary">
                        <div class="progress__bar">
                            <div class="progress__bar__fill" style="width:25%"></div>
                        </div>
                    </div>

                    <div class="progress progress--accent progress--small">
                        <div class="progress__bar">
                            <div class="progress__bar__fill" style="width:25%"></div>
                        </div>
                    </div>

                    <div class="progress progress--accent">
                        <div class="progress__bar">
                            <div class="progress__bar__fill" style="width:66%">66%</div>
                            <div class="progress__label">33%</div>
                        </div>
                    </div>
                    
                    <div class="progress progress--accent">
                        <div class="progress__bar">
                            <div class="progress__bar__fill" style="width:25%"></div>
                        </div>
                        <div class="progress__markers">
                            <span>0</span>
                            <span>250</span>
                            <span>500</span>
                            <span>750</span>
                            <span>1000</span>
                        </div>
                    </div>

                </div>
            </div>
            

            <h1>Buttons</h1>
            <a class="button button--primary" href="#">Link Button</a>
            <a class="button button--secondary" href="#">Link Button</a>
            <a class="button button--accent" href="#">Link Button</a>

            <a class="button button--accent" href="#">
                <i class="fas fa-lock"></i>
                Link with Icon
            </a>

            <a class="button button--primary disabled" href="#">Disabled Button</a>
            <a class="button button--secondary disabled" href="#">Disabled Button</a>
            <a class="button button--accent disabled" href="#">Disabled Button</a>
            
            <a class="button button--accent button--large" href="#">Large Button</a>

            <a class="button button--secondary button--fill" href="#">Fill Button</a>

            <h1>Tables</h1>
            <table class="table divided">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Rank</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>_andy</td>
                        <td>Administrator</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>_specialk</td>
                        <td>Administrator</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>Mannriah</td>
                        <td>Administrator</td>
                    </tr>
                </tbody
            </table>

            <table class="table striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Rank</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>_andy</td>
                        <td>Administrator</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>_specialk</td>
                        <td>Administrator</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>Mannriah</td>
                        <td>Administrator</td>
                    </tr>
                </tbody
            </table>

            <table class="table divided">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Rank</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>_andy</td>
                        <td>Administrator</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>_specialk</td>
                        <td>Administrator</td>
                    </tr>
                    <tr class="accent">
                        <td>3</td>
                        <td>Mannriah</td>
                        <td>Administrator</td>
                    </tr>
                    <tr class="primary">
                        <td>4</td>
                        <td>Wairoa</td>
                        <td>Administrator</td>
                    </tr>
                    <tr class="secondary">
                        <td>5</td>
                        <td>Kyle8910</td>
                        <td>Administrator</td>
                    </tr>
                    <tr class="error">
                        <td>6</td>
                        <td>05ocram05</td>
                        <td>Senior Operator</td>
                    </tr>
                    <tr class="success">
                        <td>7</td>
                        <td>Ouhai_Ruby</td>
                        <td>Senior Operator</td>
                    </tr>
                    <tr>
                        <td>8</td>
                        <td>TheOctopus</td>
                        <td>Senior Operator</td>
                    </tr>
                    <tr class="disabled">
                        <td>9</td>
                        <td>PrinceMark</td>
                        <td>Senior Operator</td>
                    </tr>
                    <tr>
                        <td>10</td>
                        <td>EstevaoBuilder</td>
                        <td>Senior Operator</td>
                    </tr>
                </tbody
            </table>


            <h1>Pagination</h1>
            <nav class="pagination">
                <ul>
                    <li><a href="#"><i class="fas fa-angle-double-left"></i></a></li>
                    <li><a href="#">2</a></li>
                    <li class="active"><a href="#">3</a></li>
                    <li><a href="#">4</a></li>
                    <li><a href="#">5</a></li>
                    <li><a href="#">6</a></li>
                    <li><a href="#"><i class="fas fa-angle-double-right"></i></a></li>
                </ul>
            </nav>

            <nav class="pagination">
                <ul>
                    <li><a href="#"><i class="fas fa-angle-double-left"></i></a></li>
                    <li><a href="#">1</a></li>
                    <li class="divider">…</li>
                    <li><a href="#">9</a></li>
                    <li><a href="#">10</a></li>
                    <li class="active"><a href="#">11</a></li>
                    <li><a href="#">12</a></li>
                    <li><a href="#">13</a></li>
                    <li class="divider">…</li>
                    <li><a href="#">24</a></li>
                    <li><a href="#"><i class="fas fa-angle-double-right"></i></a></li>
                </ul>
            </nav>
            
            <nav class="pagination">
                <ul>
                    <li class="disabled"><a href="#"><i class="fas fa-angle-double-left"></i></a></li>
                    <li class="active"><a href="#">1</a></li>
                    <li><a href="#">2</a></li>
                    <li><a href="#">3</a></li>
                    <li><a href="#"><i class="fas fa-angle-double-right"></i></a></li>
                </ul>
            </nav>
        
        </div>

        <script src="{{ mix('assets/js/app.js') }}"></script>
        
    </body>
</html>