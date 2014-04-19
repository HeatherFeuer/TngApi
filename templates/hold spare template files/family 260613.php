<!-- try FAMILY Section -->
			<?php
				
				$tngcontent = Upavadi_TngContent::instance()->init();
				
				 //get and hold current user
				$currentperson = $tngcontent->getCurrentPersonId($person['personID']);
				$person = $tngcontent->getPerson($currentperson);
				$currentuser = ($person['firstname']. $person['lastname']);
				
				?>
				
				<a href="?personId=<?php echo $person['personID']; ?>"><span style="color:#D77600; font-size:14pt">			
				<?php echo "Family of ". $currentuser; ?></span>
				</a>
	
				<?php
				//get person details
				$person = $tngcontent->getPerson($personId);
				$birthdate = $person['birthdate'];
				$birthdatetr = ($person['birthdatetr']);
				$birthplace = $person['birthplace'];
				$deathdate = $person['deathdate'];
				$deathdatetr = ($person['deathdatetr']);
				$deathplace = $person['deathplace'];
				$name = $person['firstname']. $person['lastname'];
				
				
				//get month of the events
				$currentmonth = date("m");
								
				if ($birthdatetr == '0000-00-00') {
				$birthmonth = null;
				} else {
				$birthmonth = substr($birthdatetr, -5, 2);
				}
				
	echo "birth month=". $birthmonth. "birth date=". $birthdatetr;
				If ($currentmonth == $birthmonth) { $bornClass = 'born-highlight'; 
				} else { $bornClass="";
				}
				
				if ($deathdatetr == "0000-00-00") {
				$deathmonth = null;
				} else {
				$deathmonth = substr($deathdatetr, -5, 2);
				}
				
		
				If ($currentmonth == $birthmonth) { $bornClass = 'born-highlight';
				} else { $bornClass="";
				}
		

		
						
				//get gotra
				$personRow = $tngcontent->getGotra($person['personID']);
				$gotra = $personRow['info'];
									
				
				//get familyuser
				if ($person['sex'] == 'M') {
					$sortBy = 'husborder';
				} else if ($person['sex'] == 'F') {
					$sortBy = 'wifeorder';
				} else {
					$sortBy = null;
				}
				if ($person['living'] == '0' AND $person['deathdatetr'] !== '0000-00-00') 
					{
					$deathdate = " died: " . $person['deathdate'];
					} else {
					$deathdate = " died: date unknown";
					}
					if ($person['living'] == '1') {
					$deathdate = "  (Living)";
				}
				$families = $tngcontent->getFamilyUser($person['personID'], $sortBy);
				
				?>		

<table class="form-table">
	<tbody>
		<tr>
			<td class="tdback"><?php echo "Name"; ?></td>
			<td class="tdfront"><?php echo $name. $deathdate;?></td>
			
			<td class="tdback"><?php echo "Gotra"; ?></td>
			<td class="tdfront"><?php echo $gotra;?></td></tr>
		<tr>	
			<td class="tdback"><?php echo "Born"; ?></td>
			
			<td class="tdfront <?php echo $bornClass; ?>"><?php echo $birthdate;?></td>
			
			<td class="tdback"><?php echo "Place"; ?></td>
			<td class="tdfront"><?php echo $birthplace;?></td>
		</tr>
	</tbody>
<?php
			$parents = '';
			$parents = $tngcontent->getFamilyById($person['famc']);
			//var_dump ($person['famc']);
			if ($person['famc'] !== '' and $parents['wife'] !== '') {
			$mother = $tngcontent->getPerson($parents['wife']);
			}
			if ($person['famc'] !== ''and $parents['husband'] !== '') {
			$father = $tngcontent->getPerson($parents['husband']);
			}
			
			
			
?>
	<tbody>
		<tr>
		<?php if ($father['living'] == '0' AND $father['deathdatetr'] !== '0000-00-00') 
					{
					$deathdate = " died: " . $father['deathdate'];
					} else {
					$deathdate = " died: date unknown";
					}
					if ($father['living'] == '1') {
					$deathdate = "  (Living)";
					
				}
				if ($father['personID'] == '') {
				$fathername = "Unknown";
				} else {
				$fathername = $father['firstname'] . $father['lastname']. $deathdate;
				}
				?>
				
			<td class="tdback">Father</td>
			<td class="tdfront" colspan="0">
				
			<a href="?personId=<?php echo $father['personID']; ?>">
				
					<?php echo $fathername; ?>
				</a>
			</td>
			<td class="tdback">Born</td>
			<?php
			if ($father['birthdatetr'] == "0000-00-00") {
				$fatherbirthmonth = null;
				} else {
				$fatherbirthmonth = substr($father['birthdatetr'], -5, 2);
				}
			
				If ($currentmonth == $fatherbirthmonth) { $bornClass = 'born-highlight';
				} else { $bornClass="";
				}
			
			?>
			<td class="tdfront <?php echo $bornClass; ?>"><?php echo $father['birthdate'];?></td>
		</tr>
		<tr>
		<?php 
			
				if ($mother['living'] == '0' AND $mother['deathdatet'] !== '0000-00-00') 
					{
					$deathdate = " died: " . $mother['deathdate'];
					} else {
					$deathdate = " died: date unknown";
					}
					if ($mother['living'] == '1') {
					$deathdate = "  (Living)";

				}
				if ($mother['personID'] == '') {
				$mothername = "Unknown";
				} else {
				$mothername = $mother['firstname'] . $mother['lastname']. $deathdate;
				}
				
				?>

			<td class="tdback">Mother</td>
			<td class="tdfront" colspan="0">
				<a href="?personId=<?php echo $mother['personID']; ?>">
					<?php echo $mothername; ?>
				</a>
			</td>
			<td class="tdback">Born</td>
			<?php
			if ($mother['motherbirthdatetr'] == "0000-00-00") {
				$motherbirthmonth = null;
				} else {
				$motherbirthmonth = substr($mother['birthdatetr'], -5, 2);
				}
			
			if ($currentmonth == $motherbirthmonth) { $bornClass = 'born-highlight';
				} else { $bornClass="";
				}
				
			?>
			<td class="tdfront <?php echo $bornClass; ?>"><?php echo $mother['birthdate'];?></td>
		</tr>
	</tbody>
<?php
			foreach ($families as $family):
				$marrdatetr = $family['marrdatetr'];
				$marrdate = $family['marrdate'];
				$marrplace = $family['marrplace'];
				$order = null;
				if ($sortBy && count($families) > 1) {
					$order = $family[$sortBy];
				}
				
				$spouse['personID'] == '';
				
				if ($person['personID'] == $family['wife'])
				{
				if ($family['husband'] !== '') {
				$spouse = $tngcontent->getPerson($family['husband']);
				}
				} 
				if ($person['personID'] == $family['husband']) 
				{
				if ($family['wife'] !== '') {
				$spouse = $tngcontent->getPerson($family['wife']);
				}
				
				} 
					
				
				
				
				if ($spouse['living'] == '0' AND $spouse['deathdatetr'] !== '0000-00-00') 
					{
					$deathdate = " died: " . $spouse['deathdate'];
					} else {
					$deathdate = " died: date unknown";
					}
					if ($spouse['living'] == '1') {
					$deathdate = "  (Living)";
				}
				
				if ($spouse['personID'] == '') {
				$spousename = "Unknown";
				} else {
				$spousename = $spouse['firstname'] . $spouse['lastname']. $deathdate;
				}
				//var_dump ($spousename);
				$children = $tngcontent->getChildren($family['familyID']);
				
				
			?>
		<tr>
		<td colspan="0">&</td>
		</tr>			
		<tr>
			<td class="tdback"><?php echo "Family ",$order; ?></td>
			<td class="tdfront" colspan="0"> 
				<a href="?personId=<?php echo $spouse['personID']; ?>">
					<?php echo $spousename; ?>
				</a>
			</td>
			<td class="tdback">Born</td>
			<?php
			$spousebirthmonth = substr($spouse['birthdatetr'], -5, 2);
				If ($currentmonth == $spousebirthmonth) { $bornClass = 'born-highlight';
				} else { $bornClass="";
				}
				
			?>
			<td class="tdfront <?php echo $bornClass; ?>"><?php echo $spouse['birthdate'];?></td>

		</tr>
		<tr>
		<td class="tdback"><?php echo "Married" ?></td>
		<?php
			if (marrdatetr == "0000-00-00") {
				$marrmonth = null;
				} else {
				$marrmonth = substr($family['marrdatetr'], -5, 2);
				}
			
			If ($currentmonth == $marrmonth) { $bornClass = 'born-highlight';
				} else { $bornClass="";
				}
				
			?>
			<td class="tdfront <?php echo $bornClass; ?>"><?php echo $marrdate ?>
			</td>
			<td class="tdback"><?php echo "Place"; ?></td>
			<td class="tdfront"><?php echo $marrplace;?></td>
		
		</tr>
		<tr>
			<td class="tdback">Children</td>
			<td class="tdfront" colspan="0">
			<ul>
			<?php
				foreach ($children as $child):
					$classes = array('child');
					$childPerson = $tngcontent->getPerson($child['personID']);
					$childName = $childPerson['firstname'] . $childPerson['lastname'];
					$childdeathdate = $childPerson['deathdate'];
					
					if ($child['haskids']) {
						$classes[] = 'haskids';
					}
					$class = join(' ', $classes);
			?>
			<?php 
					if ($childPerson['living'] == '0' AND $childPerson['deathdatetr'] !== '0000-00-00') 
					{
					$childdeathdate = (" died: ". $childPerson['deathdate']);
					} else {
					$childdeathdate = " died: date unknown";
					}
					if ($childPerson['living'] == '1') {
					$childdeathdate = "  (Living)";
					}
				?>
					
						
								
				<li colspan="0", class="<?php echo $class ?>">
					<a href="?personId=<?php echo $childPerson['personID']; ?>">
				
				<?php 
				if ($childPerson['birthdatetr'] == "0000-00-00") {
				$childbirthmonth = null;
				} else {
				$childbirthmonth = substr($childPerson['birthdatetr'], -5, 2);
				}
				if ($childPerson['deathdatetr'] == "0000-00-00") {
				$childdeathmonth = null;
				} else {
				$childdeathmonth = substr($childPerson['deathdatetr'], -5, 2);
				}
				
				if ($childPerson['birthdatetr'] == '0000-00-00') {
					$childbirthdate = "date unknown";
					} else {
					$childbirthdate = $childPerson['birthdate'];
					}
			
				If ($currentmonth == $childbirthmonth) {
					echo $childName; ?></a>,<span style="background-color:#E0E0F7"> born: <?php echo $childbirthdate; ?>, </span><?php echo $childPerson['birthplace']; ?><?php echo $childdeathdate; ?>
					</li> 
				<?php
				} elseif ($currentmonth == $childdeathmonth) {
				echo $childName; ?></a>, born: <?php echo $childbirthdate; ?>,<?php echo $childPerson['birthplace'];?><span style="background-color:#E0E0F7"><?php echo $childdeathdate; ?>
				</span>
				</li> 
				<?php
				} elseif (($currentmonth == $childbirthmonth) AND ($currentmonth == $childdeathmonth)) {
				echo $childName; ?></a>,<span style="background-color:#E0E0F7"> born: <?php echo $childbirthdate; ?>,<?php echo $childPerson['birthplace']; ?><span style="background-color:#E0E0F7"><?php echo $childdeathdate; ?>
				</span>
				</li>
				<?php
				} else {
				echo $childName; ?></a>, born: <?php echo $childPerson['birthdate']; ?>, <?php echo $childPerson['birthplace']; ?><?php echo $deathdate;?>
				</li>

				<?php
				}
				endforeach;
				?>
			</ul>
			</td>
		</tr>
				<?php
				endforeach;
				?>				
			</ul>
			</td>
		</tr>
		
	</tbody>
</table>				
				 
				