<?php
require_once("search_config.php");
require_once('vendor/autoload.php');
// include the guzzler library
use GuzzleHttp\Client;

// declare variables for sanity
$search_terms = $witness_type = $query = '';
// search_terms = (string) keyword search items
// witness_type = (int) type of witness (made it an int for security/validation pruposes)
// query = (string) the concatenated doccloud api request
$q = $options = [];
// q = the doccloud "q" query, stored as an array of query terms (which will be imploded with spaces when building out the api request, $query)
// options = an array holding the rest of the api request options. stored as an array for code readability and customizability (e.g. via checkboxes on the frontend)

// search options based on doccloud api
$options = [
    'data=true',
    'annotation=true',
    'per_page=100',
    'page=1',
    'mentions=3',
    'sections=true'];

// process post action
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $searchparameterposted = 0;
    // prime the query with the group name since it will be included on all searches
    $q[] = 'group:dhpraxis';

    // get post variables & sanitize it
    // Get Hearing Subject
    $hearingsubject = sanatize_posteddata($_POST['hearingsub']);
    if(strlen($hearingsubject)!=0)
    {
        $searchparameterposted = 1;
        $q[] = '"Hearing Subject":"'.$array_hearingsubjects[$hearingsubject].'"';
    }
    // Get Hearing location
    $hearinglocation = sanatize_posteddata($_POST['hearingloc']);
    if(strlen($hearinglocation)!=0)
    {
        $searchparameterposted = 1;
        $q[] = '"Hearing Location":"'.$array_hearinglocations[$hearinglocation].'"';
    }
    /*
    // Get Congress Member Name
    $congmembernamearray = $_POST['congmembername'];
    foreach($congmembernamearray as $postcongmemberkey=>$postcongmembervalue)
    {
        $congmembername = "";
        $congmembername = sanatize_posteddata($postcongmembervalue);
        if(strlen($congmembername)!=0)
        {
            $searchparameterposted = 1;
            $q[] = '"Congressional Member Name '.($postcongmemberkey+1).'":"'.$array_congressmembersnames[$congmembername].'"';
       }
    }
    // Get Staff Investigator Name
    $staffinvestigatornamearray = $_POST['staffinvestigatorname'];
    foreach($staffinvestigatornamearray as $poststaffinvestigatorkey=>$poststaffinvestigatorvalue)
    {
        $staffinvestigatorname = "";
        $staffinvestigatorname = sanatize_posteddata($poststaffinvestigatorvalue);
        if(strlen($staffinvestigatorname)!=0)
        {
            $searchparameterposted = 1;
            $q[] = '"Staff Investigator Name '.($poststaffinvestigatorkey+1).'":"'.$array_staffinvestigatorsnames[$staffinvestigatorname].'"';
       }
    }
    */
    // Get Witness Occupation
    $witnessoccupation = sanatize_posteddata($_POST['witnessoccupation']);
    if(strlen($witnessoccupation)!=0)
    {
        $searchparameterposted = 1;
        $q[] = '"Witness Occupation":"'.$array_witnessoccuptions[$witnessoccupation].'"';
    }
    // Get Witness Organizational Affiliation
    $witnessorgaffilation = sanatize_posteddata($_POST['witnessorgaffilation']);
    if(strlen($witnessorgaffilation)!=0)
    {
        $searchparameterposted = 1;
        $q[] = '"Witness Organizational Affiliation":"'.$array_witnessorgaffiliation[$witnessorgaffilation].'"';
    }
    // Get Witness Attorney
    $witnessattorney = sanatize_posteddata($_POST['witnessattorney']);
    if(strlen($witnessattorney)!=0)
    {
        $searchparameterposted = 1;
        $q[] = '"Witness Attorney":"'.$array_witnessattorney[$witnessattorney].'"';
    }
    // Get Witness Attorney
    $witnesstype = sanatize_posteddata($_POST['witnesstype']);
    if(strlen($witnesstype)!=0)
    {
        $searchparameterposted = 1;
        $q[] = '"Witness Type":"'.$array_witnesstype[$witnesstype].'"';
    }
    
    $fetcheddocuments = array();
    if($searchparameterposted==1)
    {
        // build the api request string:
        $query = 'https://www.documentcloud.org/api/search.json?'; // the api request base
        $query .= implode('&', $options); // concatenate all of the options, separated by ampersands per "get"/api request specs
        $query .= '&q=' . implode(' ',$q); // add the query terms (the &q= is the "get" variable for the query)

        // setup guzzler client and submit the query
        $client = new Client();
        $response = $client->get($query);
        // store the guzzler response as a json variable
        $json = $response->json();

        foreach($json['documents'] as $id => $doc) 
        {
            $title = $doc['title'];
            $thumbnail = $doc['resources']['thumbnail'];
            $documenturl = $doc["canonical_url"];
            $fetcheddocuments[] = array(
                                        "title"=>$title,
                                        "thumbnail"=>$thumbnail,
                                        "documenturl"=>$documenturl
                                    );
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../favicon.ico">

    <title>Digital HUAC</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/justified-nav.css" rel="stylesheet">

		<link href="css/custom.css" rel="stylesheet">
    <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
    <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->
    <script src="../../assets/js/ie-emulation-modes-warning.js"></script>
    <script src="https://code.jquery.com/jquery-1.10.2.js"></script>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>
		<div class="container">

			<div class="masthead">
        <img class="img-responsive" align="right" src="images/huaclogosmall.jpg">
        <nav>
          <ul class="nav nav-justified">
            <li class="active"><a href="index.html">Home</a></li>
            <li><a href="search.php">Search</a></li>
            <li><a href="browse.html">Browse</a></li>
            <li><a href="huac_about.html">HUAC History</a></li>
            <li><a href="project_about.html">About the Project</a></li>
            <li><a href="contact.html">Contact</a></li>
          </ul>
        </nav>
			</div>



    <div class="container">
        <!--search form-->
        <legend>Search the HUAC Transcripts</legend>
        <form class="form-horizontal" action="" method="post">
        <fieldset>

        <?php
          if ($_POST['formSubmit'] == "Submit")
          {
            $varSearch = $_POST ['search_content'];
            // Your search term is: echo $_POST ["search_content"];
            //print (["search_content"]) ;
            echo $varSearch;
        }
        ?>
            <h3>Hearing</h3>
            <div class="control-group" style="margin:0px 0px 10px 60px;overflow:hidden">
              <label class="control-label" for="searchinput" style="float:left;margin:0px;padding:0px">Hearing Subject</label>
              <div class="controls">
                  <select name="hearingsub" style="float:left;margin:0px 0px 0px 20px;">
                      <option value="">Select Subject</option>
                      <?php
                      foreach($array_hearingsubjects as $hearingsubkey=>$hearingsubvalue)
                      {
                      ?>
                            <option value="<?php echo $hearingsubkey;?>"><?php echo $hearingsubvalue;?></option>
                      <?php
                      }
                      ?>
                  </select>
              </div>
            </div>
            <div class="control-group" style="margin:0px 0px 10px 60px;overflow:hidden">
              <label class="control-label" for="searchinput" style="float:left;margin:0px;padding:0px">Hearing Location</label>
              <div class="controls" style="float:left">
                  <select name="hearingloc" style="float:left;margin:0px 0px 0px 20px;">
                      <option value="">Select Location</option>
                      <?php
                      foreach($array_hearinglocations as $hearinglockey=>$hearinglocvalue)
                      {
                      ?>
                            <option value="<?php echo $hearinglockey;?>"><?php echo $hearinglocvalue;?></option>
                      <?php
                      }
                      ?>
                  </select>
              </div>
            </div>
            <!--h3>Investigator</h3-->
            <?php
            /*
            $currentcongressman = 1;
            while($currentcongressman<=MAX_CONGRESS_MEMBERS)
            {
            ?>
                <div class="control-group" style="margin:0px 0px 10px 60px;overflow:hidden">
                  <label class="control-label" for="searchinput" style="float:left;margin:0px;padding:0px">Congressional Member Name <?php echo $currentcongressman?></label>
                  <div class="controls">
                      <select name="congmembername[]" style="float:left;margin:0px 0px 0px 20px;">
                          <option value="">Select Name</option>
                          <?php
                          foreach($array_congressmembersnames as $congmemnamekey=>$congmemnamevalue)
                          {
                          ?>
                                <option value="<?php echo $congmemnamekey;?>"><?php echo $congmemnamevalue;?></option>
                          <?php
                          }
                          ?>
                      </select>
                  </div>
                </div>
            <?php
            $currentcongressman++;
            }
            ?>
            <br/><br/>
            <?php
            $currentstaffinvestigator = 1;
            while($currentstaffinvestigator<=MAX_STAFF_INVESTIGATORS)
            {
            ?>
            <div class="control-group" style="margin:0px 0px 10px 60px;overflow:hidden">
              <label class="control-label" for="searchinput" style="float:left;margin:0px;padding:0px">Staff Investigator Name <?php echo $currentstaffinvestigator;?></label>
              <div class="controls" style="float:left">
                  <select name="staffinvestigatorname[]" style="float:left;margin:0px 0px 0px 20px;">
                      <option value="">Select Name</option>
                      <?php
                      foreach($array_staffinvestigatorsnames as $staffinvestigatornamekey=>$staffinvestigatornamevalue)
                      {
                      ?>
                            <option value="<?php echo $staffinvestigatornamekey;?>"><?php echo $staffinvestigatornamevalue;?></option>
                      <?php
                      }
                      ?>
                  </select>
              </div>
            </div>
            <?php
            $currentstaffinvestigator++;
            }
            */
            ?>
            <h3>Witness</h3>
            <div class="control-group" style="margin:0px 0px 10px 60px;overflow:hidden">
              <label class="control-label" for="searchinput" style="float:left;margin:0px;padding:0px">Witness Occupation</label>
              <div class="controls">
                  <select name="witnessoccupation" style="float:left;margin:0px 0px 0px 20px;">
                      <option value="">Select Witness Occupation</option>
                      <?php
                      foreach($array_witnessoccuptions as $witnessocckey=>$witnessoccvalue)
                      {
                      ?>
                            <option value="<?php echo $witnessocckey;?>"><?php echo $witnessoccvalue;?></option>
                      <?php
                      }
                      ?>
                  </select>
              </div>
            </div>
            <div class="control-group" style="margin:0px 0px 10px 60px;overflow:hidden">
              <label class="control-label" for="searchinput" style="float:left;margin:0px;padding:0px">Witness Organizational Affiliation</label>
              <div class="controls" style="float:left">
                  <select name="witnessorgaffilation" style="float:left;margin:0px 0px 0px 20px;">
                      <option value="">Select Witness Org. Affiliation</option>
                      <?php
                      foreach($array_witnessorgaffiliation as $witnessorgaffkey=>$witnessorgaffvalue)
                      {
                      ?>
                            <option value="<?php echo $witnessorgaffkey;?>"><?php echo $witnessorgaffvalue;?></option>
                      <?php
                      }
                      ?>
                  </select>
              </div>
            </div>
            <div class="control-group" style="margin:0px 0px 10px 60px;overflow:hidden">
              <label class="control-label" for="searchinput" style="float:left;margin:0px;padding:0px">Witness Attorney</label>
              <div class="controls" style="float:left">
                  <select name="witnessattorney" style="float:left;margin:0px 0px 0px 20px;">
                      <option value="">Select Witness Attorney</option>
                      <?php
                      foreach($array_witnessattorney as $witnessattorneykey=>$witnessattorneyvalue)
                      {
                      ?>
                            <option value="<?php echo $witnessattorneykey;?>"><?php echo $witnessattorneyvalue;?></option>
                      <?php
                      }
                      ?>
                  </select>
              </div>
            </div>
            <div class="control-group" style="margin:0px 0px 10px 60px;overflow:hidden">
              <label class="control-label" for="searchinput" style="float:left;margin:0px;padding:0px">Witness Type</label>
              <div class="controls" style="float:left">
                  <select name="witnesstype" style="float:left;margin:0px 0px 0px 20px;">
                      <option value="">Select Witness Type</option>
                      <?php
                      foreach($array_witnesstype as $witnesstypekey=>$witnesstypevalue)
                      {
                      ?>
                            <option value="<?php echo $witnesstypekey;?>"><?php echo $witnesstypevalue;?></option>
                      <?php
                      }
                      ?>
                  </select>
              </div>
            </div>
            <!-- Button -->
            <div class="control-group">
              <label class="control-label" for="singlebutton"></label>
              <div class="controls">
                <button id="singlebutton" name="singlebutton" class="btn btn-custom">Search</button>
              </div>
            </div>

            </fieldset>
            </form>
        
        <?php
        //if (count($fetcheddocuments)!=0)
        if ($_SERVER['REQUEST_METHOD'] == 'POST')
        {
        ?>
        <div style="margin-top:20px" id="searchresult">
            <?php
            if (count($fetcheddocuments)==0)
            {
            ?>
            <h4 style="color:#d70000">Check search parameters, no documents found for your search parameters.<br/>Try again</h4>
            <?php
            }
            else
            {
            ?>
            <table class="searchtable">
                <tr class="searchtblheaderrow">
                    <th class="searchtblheadercol">ID</th>
                    <th class="searchtblheadercol">Thumbnail</th>
                    <th class="searchtblheadercol">Title</th>
                </tr>
            <?php 
                foreach($fetcheddocuments as $documentkey => $documentarray) 
                { 
            ?>
                <tr class="searchtblrow">
                    <td class="searchtblcol <?php if((($documentkey+1)%2)==0){ echo "searchtblaltcol";}?>" style="text-align:center">
                        <?php echo $documentkey+1; ?>
                    </td>
                    <td class="searchtblcol <?php if((($documentkey+1)%2)==0){ echo "searchtblaltcol";}?>">
                        <a href="<?php echo $documentarray["documenturl"]?>" target="_blank">
                            <img src="<?php echo $documentarray['thumbnail']; ?>">
                        </a>
                    </td>
                    <td class="searchtblcol <?php if((($documentkey+1)%2)==0){ echo "searchtblaltcol";}?>">
                        <a href="<?php echo $documentarray["documenturl"]?>" target="_blank">
                            <?php echo $documentarray['title']; ?>
                        </a>
                    </td>
                </tr>
            <?php 
                } 
            ?>
            </table>
            <?php
            }
            ?>
        </div>
        <script>
            $( document ).ready(function() {
                var divID = '#searchresult';
                $('html, body').animate({
                    scrollTop: $(divID).offset().top-100
                }, 2000);
            });
        </script>
        <?php
        }
        ?>
    </div>



<!-- Site footer -->
<footer class="footer">
  <a rel="license" href="http://creativecommons.org/licenses/by-nc-sa/4.0/"><img alt="Creative Commons License" style="border-width:0" src="https://i.creativecommons.org/l/by-nc-sa/4.0/88x31.png" /></a><br /><span xmlns:dct="http://purl.org/dc/terms/" property="dct:title">Digital HUAC</span> by <a xmlns:cc="http://creativecommons.org/ns#" href="www.digitalhuac.com" property="cc:attributionName" rel="cc:attributionURL">Digital HUAC</a> is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-nc-sa/4.0/">Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License</a>.
  <p>The Graduate Center / City University of New York / 2015</p>
</footer>

</div> <!-- /container -->


<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
<script src="../../assets/js/ie10-viewport-bug-workaround.js"></script>
</body>
</html>
