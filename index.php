<!DOCTYPE html
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Vocabulary</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <link href="css/bootstrap.css" rel="stylesheet">
    <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
      .sidebar-nav {
        padding: 9px 0;
      }
    </style>
    <link href="css/bootstrap-responsive.css" rel="stylesheet">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- Fav and touch icons -->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
    <script src="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/js/bootstrap.min.js"></script>
    <script type="text/javascript">
      $(document).ready(function() {
        // Hide definitions on page load
        $('.definitions').toggle();

        // Bind click to nav link to toggle all definitions
        $('#toggleall').click(function() {
          $('.definitions').toggle();
        });

        // Bind click to each word div --- toggle individual word
        $('.hero-unit').click(function() {
          $(this).children('.definitions').toggle();
        });

        // Bind hover to each word div --- show more info link
        $('.hero-unit').hover(
          function() {
            $(this).children('.moreinfo').css('visibility','visible');
          },
          function() {
            $(this).children('.moreinfo').css('visibility', 'hidden');
          }
        );

        // Bind click to nav link to sort by date
        $('#sortdate').click(function() {
          // Get and sort all of the word divs
          var wordList = $('.span3');
          wordList.sort(function(a,b) {
            return $(a).attr('rel').localeCompare($(b).attr('rel')) * -1;
          });
          
          // Hide the old word divs and the footer
          $('.row-fluid').hide();
          var footer = $('footer');

          // Add the words to the page
          for (var i=0; i<wordList.length; i++) {
            if (i % 4 == 0) {
              $('.container-fluid').append('<div class="row-fluid">');
            }
            $('.row-fluid:last').append(wordList[i]);

            if (i % 4 == 3) {
              $('.container-fluid').append('</div>');
            }
          }
          // Reattach the footer below everything
          $('.container-fluid:last').append(footer);
        });
 
        // Bind click to nav link to sort alphabetically
        $('#sortaz').click(function() {
          // Get and sort all of the word divs
          var wordList = $('.span3');
          wordList.sort(function(a,b) {
            return $(a).attr('id').localeCompare($(b).attr('id')) ;
          });

          // Hide the old word divs and the footer
          $('.row-fluid').hide();
          var footer = $('footer');

          // Add the words to the page
          for (var i=0; i<wordList.length; i++) {
            if (i % 4 == 0) {
              $('.container-fluid').append('<div class="row-fluid">');
            }
            $('.row-fluid:last').append(wordList[i]);

            if (i % 4 == 3) {
              $('.container-fluid').append('</div>');
            }
          }
          // Reattach the footer below everything
          $('.container-fluid:last').append(footer);
        });

        // Bind change to nav select to filter by chosen letter
        $('#charselect').change(function() {
          var letter = $(this).val();
          var wordList = $('.span3');

          $('.row-fluid').hide();
          var footer = $('footer');

          // Filter out non-matching words
          if (letter != '-') {
            wordList = wordList.filter(function (index) {
              return wordList[index].id.substr(0,1) == letter;
            });
          }
          
          // Add the words to the page
          for (var i=0; i<wordList.length; i++) {
            if (i % 4 == 0) {
              $('.container-fluid').append('<div class="row-fluid">');
            }
            $('.row-fluid:last').append(wordList[i]);

            if (i % 4 == 3) {
              $('.container-fluid').append('</div>');
            }
          }
          // Reattach the footer below everything
          $('.container-fluid:last').append(footer);
        });
      });
    </script>

    <link rel="shortcut icon" href="ico/favicon.ico">
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="ico/apple-touch-icon-57-precomposed.png">
  </head>

  <body>
    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container-fluid">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <a class="brand" href=".">Vocabulary</a>
          <div class="nav-collapse collapse">
            <p class="navbar-text pull-right">
             SOME NAME HERE
            </p>
            <ul class="nav">
              <li><a href="#" id="toggleall" >#Toggle Definitions</a></li>
              <li><a href="#" id="sortaz" >#Sort A-Z</a></li>
              <li><a href="#" id="sortdate" >#Sort by Date</a></li>
              <li><a href="#">#Filter:</a></li>
              <li>
                <select id="charselect" style="width:55px; margin-top:5px; margin-bottom:5px;">
                  <?php
                    $letters = array('-','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
                    foreach($letters as $key => $letter) {
                      echo "<option value='" . $letter . "'>" . $letter  . "</option>";
                    }
                  ?>
                </select></a>
              </li>
            </ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>

    <div class="container-fluid">

    <?php

      $json = file_get_contents("http://localhost:5984/grevocab/_all_docs?include_docs=true"); #Enter CouchDB URL (http://...) that returns all words in JSON format
      $jsonb = json_decode($json, true);
      $jsonc = $jsonb['rows'];

      $columns = 4;
      $counter = 0; // Number of words processed
	
      // Iterate over each word
      foreach ($jsonc as $word => $value) {

        // Start the row <div>
        if ( ($counter % $columns) == 0 ) {
          echo '<div class="row-fluid">';
        }
		
        echo '<div class="span3" id="' . $value['doc']['word'] . '" rel="' . $value['doc']['time'] . '"><div class="hero-unit">';
        echo '<h2>' . $value['doc']['word'] . '</h2>';

        echo '<ul class="definitions">';

        $definitions = $value['doc']['definitions'];
	
        foreach ($definitions as $definition) {
          echo '<li>' . $definition . '</li>';
        }
        echo '</ul>';

        echo '<span class="moreinfo" style="visibility:hidden; float:right; font-size:.7em; color:#BBB">' . substr($value['doc']['time'], 0, 11) . ' - ';
        echo '<a style="color:#bbb;" target="_blank" href="http://www.google.com/search?gs_ivs=1&tts=1&q=define+' . $value['doc']['word'] . '">more info</a></span>';

        echo '</div></div>'; //End hero-unit and span divs

        // End the row <div>
	if ( ($counter % $columns) == ($columns - 1) ) {
	  echo '</div>';
        }

	$counter = $counter + 1;
      }
    ?>

    </div><!--/.fluid-container-->

    <footer>
      <p><?php echo $counter ?> total words - Vocabulary 2014</p>
    </footer>

  </body>
</html>
