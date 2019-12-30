<?php
 /*    This program is free software; you can redistribute it and/or
 *    modify it under the terms of the GNU General Public License
 *    as published by the Free Software Foundation; either version 2
 *    of the License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. -->
 */

use OpenEMR\Core\Header;

include_once("../../globals.php");
include_once("$srcdir/api.inc");

formHeader("Form: SJI STRIDE Intake");
$returnurl = 'encounter_top.php';
/* name of this form */
$form_name = "sji_stride_intake";

if (!$pid) {
    $pid = $_SESSION['pid'];
}

// get the record from the database
if (!empty($_GET['id'])) {
	$obj = formFetch("form_".$form_name, $_GET["id"]);
 }

// Add on pronouns
$query = "select pronouns from form_sji_intake_core_variables where pid=? order by id desc limit 1";
$res = sqlStatement($query, array($pid));
$pronouns = array();
while ($row = sqlFetchArray($res)) {
   $pronouns[] = $row['pronouns'];
}
if (sizeof($pronouns)) {
   $obj['pronouns'] = $pronouns;
}

// Add on supportive_people list
$query = "select id from form_sji_intake where pid = ? order by id DESC limit 1";
$res = sqlStatement($query, array($pid));
$intake = sqlFetchArray($res);
$intake_id = $intake['id'];
if (isset($intake_id)) {
   $query = "select supportive_people from form_sji_intake_supportive_people where pid=?";
   $res = sqlStatement($query, array($intake_id));
   $supportive_people = array();

   while ($row = sqlFetchArray($res)) {
      $supportive_people[] = $row['supportive_people'];
   }
   if (sizeof($supportive_people)) {
      $obj['supportive_people'] = $supportive_people;
   }
}

// TODO: the stride intake form additionaly asks for the dosage.  Is there a way
// we can easil add that?
// Add on hormones_types list
$query = "select hormones_types from form_sji_intake_hormones_types where pid=?";
$res = sqlStatement($query, array($intake_id));
$hormones_types = array();
while ($row = sqlFetchArray($res)) {
   $hormones_types[] = $row['hormones_types'];
}
if (sizeof($hormones_types)) {
   $obj['hormones_types'] = $hormones_types;
   $obj['taken_hormones'] = 'Yes';
} else {
   $obj['taken_hormones'] = 'No';
}

/* remove the time-of-day from the date fields */
if (!empty($obj['date_of_signature'])) {
	$dateparts = explode(" ", $obj['date_of_signature']);
	$obj['date_of_signature'] = $dateparts[0];
}

// TODO: figure out how to get values from the object selected
/* A helper function for getting list options */
function getListOptions($list_id, $fieldnames = array('option_id', 'title', 'seq')) {
    global $obj;

    $output = "";
    $selected = array();
    if (isset($obj[$list_id])) {
           $selected = $obj[$list_id];
    }
    $query = sqlStatement("SELECT ".implode(',', $fieldnames)." FROM list_options where list_id = ? AND activity = 1 order by seq", array($list_id));
    while ($list_options = sqlFetchArray($query)) {
        $output .= '<option value="'. $list_options['option_id'] .'" ';

        if (isset($selected)) {

           if (is_array($selected) && in_array($list_options['option_id'], $selected)) {
              $output .= "selected ";
              $key = array_search($list_options['option_id'], $selected);
              if ($key >= 0) {
                 array_splice($selected, $key, 1);
              }
           } else if ($selected === $list_options['option_id']) {
              $output .= "selected ";
              unset($selected);
           }
        }
        $output .= '>'. $list_options['title'] .'</option>';
    }

    if (isset($selected)) {
       if (is_array($selected)) {
          foreach ($selected as $selection) {
             $output .= '<option value="'. $selection .'" selected>'. $selection .'</option>';
          }
       } else if (strlen($selected)) {
          $output .= '<option value="'. $selected .'" selected>'. $selected .'</option>';
       }
    }

    return $output;
}

function getListOptionsJson($list_id, $fieldnames = array('option_id', 'title', 'seq')) {
    global $obj;
    $output = "[";
    $query = sqlStatement("SELECT ".implode(',', $fieldnames)." FROM list_options where list_id = ? AND activity = 1 order by seq", array($list_id));
    while ($list_options = sqlFetchArray($query)) {
        $output .= '{ value: "'. $list_options['option_id'] .'", name: "'. $list_options['option_id'] .'" },'. "\n";
    }

    return $output . "]";
}

?>

<html><head>
<?php Header::setupHeader(['bootstrap', 'select2']); ?>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

<script language="JavaScript">
// required for textbox date verification
var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';
</script>

</head>

<body class="body_top">
<div class="container">

<div class="row bg-primary">
<div class="col-sm-12">
<h2 class="text-center"><?php echo xlt('St. James Infirmary STRIDE Intake'); ?></h2>
</div>
</div>

<form class="form-horoizontal" role="form" method=post action="<?php 
echo $rootdir."/forms/".$form_name."/save.php?mode=";
if (isset($_GET['id'])) {
   echo "update&id=".attr($_GET["id"]);
} else {
   echo "new";
}
?>" name="my_form" id="my_form">

<!-- pronouns -->
<div class="form-group row">
<label class="col-sm-6 control-label" for="pronouns"><?php echo xlt('Pronouns'); ?></label>
<div class="col-sm-6">
<select id="pronouns" type=text name="pronouns" class="form-control select2">
<option></option>
<?php echo getListOptions('pronouns'); ?>
</select>
</div>
</div>
<!-- pronouns -->

<div class="form-group row">
<label class="col-sm-6 control-label" for="why_are_you_here">What brought you to the transgender care program today?</label>
<div class="col-sm-6">
<textarea class="form-control" rows=3 id="why_are_you_here" name="why_are_you_here">
<?php
if (isset($obj['why_are_you_here'])) {
   echo $obj['why_are_you_here'];
}
?>
</textarea>
</div>
</div>

<!-- TODO: setup js to expend or collapse this section depending on the value of the selection -->
<div class="form-group row">
<label class="col-sm-6 control-label" for="taken_hormones">Have you taken hormones?</label>
<div class="col-sm-6">
<select class="select2 form-control" id="taken_hormones" name="taken_hormones" data-placeholder="Have you taken hormones?">
<option></option>
<?php echo getListOptions('taken_hormones'); ?>
</select>
</div>
</div>

<div id="taken_hormones_questions">

<div class="form-group row">
<label class="col-sm-6 control-label" for="hormone_duration">For how long?</label>
<div class="col-sm-6">
<input class="form-control" type=text id="hormone_duration" name="hormone_duration">
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="hormone_form_dosage">What form/dosage?</label>
<div class="col-sm-6">
<input class="form-control" type=text id="hormone_form_dosage" name="hormone_form_dosage">
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="hormone_program">Through which program?</label>
<div class="col-sm-6">
<input class="form-control" type=text id="hormone_program" name="hormone_program">
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="why_stopped">Why did you stop?</label>
<div class="col-sm-6">
<input class="form-control" type=text id="hormone_program" name="hormone_program">
</div>
</div>

</div> <!-- taken_hormones_questions -->

<div class="form-group row">
<label class="col-sm-6 control-label" for="why_continue">Why do you want to [continue to] take hormones?</label>
<div class="col-sm-6">
<textarea class="form-control" rows=3 id="why_continue" name="why_continue">
<?php
if (isset($obj['why_continue'])) {
   echo $obj['why_continue'];
}
?>
</textarea>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="affect_expectations">How do you think taking hormones will [continue to] affect you physically and mentally?</label>
<div class="col-sm-6">
<textarea class="form-control" rows=3 id="affect_expectations" name="affect_expectations">
<?php
if (isset($obj['affect_expectations'])) {
   echo $obj['affect_expectations'];
}
?>
</textarea>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="effect_hopes">What are some of the effects of taking hormones you feel particularly hopeful about? [Any new or differet goals?]</label>
<div class="col-sm-6">
<textarea class="form-control" rows=3 id="effect_hopes" name="effect_hopes">
<?php
if (isset($obj['effect_hopes'])) {
   echo $obj['effect_hopes'];
}
?>
</textarea>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="hormone_concerns">What [new or different] concerns do you have about taking hormones?)</label>
<div class="col-sm-6">
<textarea class="form-control" rows=3 id="hormone_concerns" name="hormone_concerns">
<?php
if (isset($obj['hormone_concerns'])) {
   echo $obj['hormone_concerns'];
}
?>
</textarea>
</div>
</div>

<hr>

<div class="row bg-primary">
<div class="col-sm-12">
<h3 class=""><?php
   echo xlt(
      'We would like to know a bit more about how hormones would fit into '.
      'your life.  Nothing you say in this section will impact the services '.
      'you recieve - we just want to explore your situation.' 
   );
?></h2>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="who_out_to">Who in your life are you out to as trans? (work/school/family/friends/housing situation?)?</label>
<div class="col-sm-6">
<textarea class="form-control" rows=3 id="who_out_to" name="who_out_to">
<?php
if (isset($obj['who_out_to'])) {
   echo $obj['who_out_to'];
}
?>
</textarea>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="supportive_people">When times are rough where/who do you go to for support?</label>
<div class="col-sm-6">
<select class="select2 form-control" id="supportive_people" multiple=multiple name="supportive_people[]">
<option></option>
<?php echo getListOptions('supportive_people'); ?>
</select>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="financial_situation">Tell me about your financial situation (Will you be able to work while transitioning?  Cost of hormones?)</label>
<div class="col-sm-6">
<textarea class="form-control" rows=3 id="financial_situation" name="financial_situation">
<?php
if (isset($obj['financial_situation'])) {
   echo $obj['financial_situation'];
}
?>
</textarea>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="safety_concerns">What safety concerns do you have around being trans? How do you imagine hormones will effect this?</label>
<div class="col-sm-6">
<textarea class="form-control" rows=3 id="safety_concerns" name="safety_concerns">
<?php
if (isset($obj['safety_concerns'])) {
   echo $obj['safety_concerns'];
}
?>
</textarea>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="useful_support">What other kinds of support would be useful to you?</label>
<div class="col-sm-6">
<textarea class="form-control" rows=3 id="useful_support" name="useful_support">
<?php
if (isset($obj['useful_support'])) {
   echo $obj['useful_support'];
}
?>
</textarea>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="clinician_narrative">Narrative for clinician</label>
<div class="col-sm-6">
<textarea rows=3 type=text class="sm-textarea form-control" id="clinician_narrative" name="clinician_narrative">
<?php
if (isset($obj['clinician_narrative'])) {
   echo $obj['clinician_narrative'];
}
?>
</textarea>
</div> <!-- id="clinician_narrative" -->
</div>
</div>

<?php
// commented out below private field, because no field in database, and causes error.
?>
<!--
<label class="col-sm-6 control-label" for="private">This note is private</label>
<input type="checkbox" name="private" id="private">
<br>

<br>
<b><?php echo xlt('Signature:'); ?></b>
<br>
-->

<div style="margin: 10px;">
<input type="button" class="save" value="    <?php echo xla('Save'); ?>    "> &nbsp;
<input type="button" class="dontsave" value="<?php echo xla('Don\'t Save'); ?>"> &nbsp;
</div>

</form>
</div> <!-- id="container" -->

</body>

<script language="javascript">

// jQuery stuff to make the page a little easier to use

$(document).ready(function(){
    $(".save").click(function() { top.restoreSession(); $('#my_form').submit(); });
    $(".dontsave").click(function() { parent.closeTab(window.name, false); });
    //$("#printform").click(function() { PrintForm(); });

    // If the participant has taken hormones then show the related questions
    $('#taken_hormones_questions').hide();
    var taken_hormones = $('#taken_hormones').val();
    if ( taken_hormones == 'Yes' ) {
       $('#taken_hormones_questions').fadeIn('slow');
    }

    // A UI helper function that allows us to hide the hormones questions if the participant refused it
    $('#taken_hormones').change(function() {
       if (this.value == 'Yes') {
          $('#taken_hormones_questions').fadeIn('slow');
       } else {
          $('#taken_hormones_questions').fadeOut('slow');
       }
    });

    $('.select2').select2({
       tags: true,
    });

});

</script>

</html>
