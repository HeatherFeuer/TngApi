<!-- Death Anniversaries Modified for BootStrap March 2016-->
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Family Anniversaries</title>
</head>
<!---- Jquery date picker strat -->
<link rel="stylesheet" href="<?php echo plugins_url('css/jquery-ui-1.10.4.custom.css', dirname(__FILE__)); ?>" rel="stylesheet" type="text/css">
<script src="<?php echo plugins_url('js/jquery-1.10.2.js', dirname(__FILE__)); ?>" type="text/javascript"></script>
<script src="<?php echo plugins_url('js/jquery-datepicker.min.js', dirname(__FILE__)); ?>" type="text/javascript"></script>
<!-- <script src="<?php echo plugins_url('js/jquery-datepicker.custom.js', dirname(__FILE__)); ?>" type="text/javascript"></script>
-->
<script type="text/javascript">
$(function() {
    $('.date-picker').datepicker( {
        changeMonth: true,
        changeYear: true,
        showButtonPanel: true,
        dateFormat: '01/mm/yy',
        onClose: function(dateText, inst) {
            var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
            var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
            $(this).datepicker('setDate', new Date(year, month, 1));
			
        }
    });
});
</script>
<style>
.ui-datepicker-calendar { display: none; }
.ui-datepicker .ui-datepicker-buttonpane  { display: none; }
</style>
</head>
<!---- Jquery date picker end -->
<!-- below is to get month selector -->
<body>
<form style="display: inline-block;" method="get">
	<label for="search-month">Click to select Month and Year: <input type="text" value="<?php echo $monthyear; ?>" name="monthyear" id="search-monthyear" class="date-picker" /></label> 
<!-- <label for="search-year">Enter Year: <input type="text" value="<?php echo $year; ?>" name="year" id="search-year" size="4"></label>
-->
<input type="submit" value="Update" style="width:85px;" />
</form>
<!-- above is to get month selector -->
<p><br/></P>
<h2><span style="color:#D77600; font-size:25px">Death Anniversaries for <?php echo $date->format('F Y'); ?></span></h2>
Clicking on a name takes you to the Individual's FAMILY Page.</br>
	<?php
	//get and hold current user
	$tngcontent = Upavadi_tngcontent::instance()->init();
	$user = $tngcontent->getTngUser();
	$usertree = $user['gedcom'];
	?>
<div class="container col-md-12 table-responsive">
<table class="table table-bordered"> 
	<tr class="row">
		<td class="tdback col-md-3" style="text-align: center">Name</td>
		<td class="tdback col-md-3">Date</td>
		<td class="tdback col-md-2">Death Place</td>
		<td class="tdback col-md-2" style="text-align: center">Years</td>
		<td class="tdback col-md-1" style="text-align: center">Age at Death</td>
			
		<?php 
		$url = $tngcontent->getTngUrl();
			if ($usertree == '') { ?>
		<td class="tdback col-md-1">Tree</td>
				
		<?php } ?>
	</tr>
    
			
<tbody>
	<?php 
	foreach ($danniversaries as $danniversary): 
		$danniversarydate = strtotime($danniversary['deathdate']);
		$Years = $year - date('Y', $danniversarydate);
		$tree = $danniversary['gedcom'];
		//get age at death
		if ($danniversary['birthdatetr'] !== "0000-00-00") {
		$d_birtharray = explode("-", ($danniversary['birthdatetr']));
		$d_birthyear = $d_birtharray[0];
		$d_birthmonth = $d_birtharray[1];
		$d_birthday = $d_birtharray[2];
		$deatharray = explode("-", ($danniversary['deathdatetr']));
		$deathyear = $deatharray[0];
		$deathmonth = $deatharray[1];
		$deathday = $deatharray[2];
		$setBirthdate = new DateTime();
		$setBirthdate->setDate($d_birthyear, $d_birthmonth, $d_birthday);
		$setDeathdate = new DateTime();
		$setDeathdate->setDate($deathyear, $deathmonth, $deathday);
		$setBirthdate->format('c') . "<br / >\n";
		$setDeathdate->format('c') . "<br / >\n";
		$i = $setBirthdate->diff($setDeathdate);
		$i->format("%Y");
		$ageAtDeath = $i->format("%Y");
		}	else { 	$ageAtDeath = "";
		}	
		$photos = $tngcontent->getTngPhotoFolder();
		$personId = $danniversary['personid'];
		$defaultmedia = $tngcontent->getDefaultMedia($personId, $tree);

		$photosPath = $url. $photos;
		$mediaID = $photosPath."/". $defaultmedia['thumbpath'];
	?>
		<tr class="row">
			<td class="col-md-3" style="text-align: center">
			<div>
			<?php if ($defaultmedia['thumbpath']) { ?>
			<img src="<?php 
			echo "$mediaID";  ?>" border='1' height='50' border-color='#000000'/> <?php } ?>
			<br /><a href="/family/?personId=<?php echo $danniversary['personid']; ?>&amp;tree=<?php echo $tree; ?>">
			<?php echo $danniversary['firstname']. " "; echo $danniversary['lastname']; ?></a></div></td>
			<td class="col-md-3"><?php echo $danniversary['deathdate']; ?></td>
			<td class="col-md-2"><?php echo $danniversary['deathplace']; ?></td>
			<td class="col-md-2" style="text-align: center"><?php echo $Years ?></td>
			<td class="col-md-1" style="text-align: center"><?php echo $ageAtDeath; ?> </td>
			<?php 
		if ($usertree == '') { ?>
			<td class="col-md-1"><?php echo $danniversary['gedcom']; ?></td>
        </tr>
		<?php 
			}
	endforeach; 
	?>
</tbody>
</table>
</div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
   
</html>	



