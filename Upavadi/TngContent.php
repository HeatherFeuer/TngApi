<?php

class Upavadi_TngContent
{

    private static $instance = null;
    protected $db;
    protected $currentPerson;
    protected $tables = array();
    protected $sortBy = null;
    protected $tree;
    protected $custom;
    /**
     * @var Upavadi_Shortcode_AbstractShortcode[]
     */
    protected $shortcodes = array();
    protected $domain;
    private $tngUser;

    protected function __construct()
    {
        
    }

    public static function instance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Add shortcodes
     */
    public function addShortcode(Upavadi_Shortcode_AbstractShortcode $shortcode)
    {
        $this->shortcodes[] = $shortcode;
    }

    public function initPlugin()
    {
        $templates = new Upavadi_Templates();
        foreach ($this->shortcodes as $shortcode) {
            $shortcode->init($this, $templates);
        }
    }

    public function getTngPath()
    {
        return esc_attr(get_option('tng-api-tng-path'));
    }

    public function getTngIntegrationPath()
    {
        return esc_attr(get_option('tng-base-tng-path'));
    }
    
    public function getTngTables()
    {
        return $this->tables;
    }

    public function initTables()
    {
        $tngPath = $this->getTngPath();
        $configPath = $tngPath . DIRECTORY_SEPARATOR . "config.php";
        include $configPath;
        $vars = get_defined_vars();
        foreach ($vars as $name => $value) {
            if (preg_match('/_table$/', $name)) {
                $this->tables[$name] = $value;
            }
            if (preg_match('/tngdomain$/', $name)) {
                $this->domain = $value;
            }
        }
    }

    public function getDomain()
    {
        return $this->domain;
    }

    public function guessVersion()
    {
        $sql = 'describe ' . $this->tables['people_table'];
        $result = $this->query($sql);
        $version = 9;
        while ($row = $result->fetch_assoc()) {
            if ($row['Field'] == 'burialtype') {
                $version = 10;
                break;
            }
        }
        return $version;
    }

    /**
     * @return mysqli
     */
    public function getDbLink()
    {
        return $this->db;
    }

    public function init()
    {
        if ($this->db) {
            return $this;
        }

        if ($this->currentPerson) {
            return $this;
        }

        // get_currentuserinfo();


        $dbHost = esc_attr(get_option('tng-api-db-host'));
        $dbUser = esc_attr(get_option('tng-api-db-user'));
        $dbPassword = esc_attr(get_option('tng-api-db-password'));
        $dbName = esc_attr(get_option('tng-api-db-database'));
        $EventID = esc_attr(get_option('tng-api-tng-event'));
        $db = mysqli_connect($dbHost, $dbUser, $dbPassword);
        mysqli_select_db($db, $dbName);
        $this->db = $db;
        $this->initTables();

        $tng_user_name = $this->getTngUserName();
        $query = "SELECT * FROM {$this->tables['users_table']} WHERE username='{$tng_user_name}'";
        $result = mysqli_query($db, $query) or die("Cannot execute query: $query");
        $row = $result->fetch_assoc();

        $this->currentPerson = $row['personID'];
        return $this;
    }

    public function query($sql)
    {
        $result = mysqli_query($this->db, $sql) or die("Cannot execute query: $sql");
        return $result;
    }

    public function initAdmin()
    {
        register_setting('tng-api-options', 'tng-api-email');
        register_setting('tng-api-options', 'tng-api-tng-event');
        register_setting('tng-api-options', 'tng-api-tng-page-id');
        register_setting('tng-api-options', 'tng-api-tng-path');
        register_setting('tng-api-options', 'tng-api-tng-photo-upload');
        register_setting('tng-api-options', 'tng-api-db-host');
        register_setting('tng-api-options', 'tng-api-db-user');
        register_setting('tng-api-options', 'tng-api-db-password');
        register_setting('tng-api-options', 'tng-api-db-database');

        add_settings_section('general', 'General', function() {
            
        }, 'tng-api');

        add_settings_field('tng-email', 'Notification Email Address', function () {
            $tngEmail = esc_attr(get_option('tng-api-email'));
            echo "<input type='text' name='tng-api-email' value='$tngEmail' />";
        }, 'tng-api', 'general');
        add_settings_section('tng', 'TNG', function() {
            echo "In order for the plugin to work we need to know where the original TNG source files live";
        }, 'tng-api');
        add_settings_field('tng-path', 'TNG Path', function () {
            $tngPath = esc_attr(get_option('tng-api-tng-path'));
            echo "<input type='text' name='tng-api-tng-path' value='$tngPath' />";
        }, 'tng-api', 'tng');
        add_settings_field('tng-photo-upload', 'TNG Collection ID for Photo Uploads', function () {
            $tngPath = esc_attr(get_option('tng-api-tng-photo-upload'));
            echo "<input type='text' name='tng-api-tng-photo-upload' value='$tngPath' />";
        }, 'tng-api', 'tng');
        $this->init();
        $events = $this->getEventList();
        add_settings_field('tng-event', 'TNG Event to Track', function () use ($events) {
            $tngEvent = esc_attr(get_option('tng-api-tng-event'));

            echo '<select name="tng-api-tng-event">';
            echo '<option value="">Do not Track</option>';

            foreach ($events as $event) {
                $eventId = $event['eventtypeID'];
                $selected = null;
                if ($eventId == $tngEvent) {

                    $selected = "selected='selected'";
                }
                echo "<option value='$eventId' $selected>{$event['display']}</option>";
            }
            echo '</select>';
        }, 'tng-api', 'tng');
        add_settings_section('db', 'Database', function() {
            echo "We also need to know where the TNG database lives";
        }, 'tng-api');
        add_settings_field('db-host', 'Hostname', function () {
            $dbHost = esc_attr(get_option('tng-api-db-host'));
            echo "<input type='text' name='tng-api-db-host' value='$dbHost' />";
        }, 'tng-api', 'db');
        add_settings_field('db-user', 'Username', function () {
            $dbUser = esc_attr(get_option('tng-api-db-user'));
            echo "<input type='text' name='tng-api-db-user' value='$dbUser' />";
        }, 'tng-api', 'db');
        add_settings_field('db-password', 'Password', function () {
            $dbPassword = esc_attr(get_option('tng-api-db-password'));
            echo "<input type='password' name='tng-api-db-password' value='$dbPassword' />";
        }, 'tng-api', 'db');
        add_settings_field('db-database', 'Database Name', function () {
            $dbName = esc_attr(get_option('tng-api-db-database'));
            echo "<input type='text' name='tng-api-db-database' value='$dbName' />";
        }, 'tng-api', 'db');
    }

    public function adminMenu()
    {
        add_options_page(
            "Options", "TngApi", "manage_options", "tng-api", array($this, "pluginOptions")
        );
    }

    function pluginOptions()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        ?>
        <form method="POST" action="options.php">
            <?php
            settings_fields('tng-api-options'); //pass slug name of page, also referred
            //to in Settings API as option group name
            do_settings_sections('tng-api');  //pass slug name of page
            submit_button();
            ?>
        </form>

        <?php
    }

    public function getCurrentPersonId()
    {
        return $this->currentPerson;
    }

    public function getPerson($personId = null, $tree = null)
    {
        if (!$personId) {
            $personId = $this->currentPerson;
        }

        $user = $this->getTngUser();

        $gedcom = $user['gedcom'];
        // If we are searching, enter $tree value
        if ($tree) {
            $gedcom = $tree;
        }

        if ($gedcom == '' && $tree) {
            $gedcom = $tree;
        }
        $treeWhere = null;
        if ($gedcom) {
            $treeWhere = ' AND gedcom = "' . $gedcom . '" AND private = 0';
        }

        $sql = <<<SQL
SELECT *
FROM {$this->tables['people_table']}
WHERE personID = '{$personId}'
{$treeWhere}
SQL;
        $result = $this->query($sql);
        $row = $result->fetch_assoc();
        return $row;
    }

    public function getFamily($personId = null, $tree = null)
    {

        if (!$personId) {
            $personId = $this->currentPerson;
        }
        $user = $this->getTngUser();
        $gedcom = $user['gedcom'];
        // If we are searching, enter $tree value
        if ($tree) {
            $gedcom = $tree;
        }

        $treeWhere = null;
        if ($gedcom) {
            $treeWhere = ' AND gedcom = "' . $gedcom . '" AND private = 0';
        }
        $sql = <<<SQL
SELECT *
FROM {$this->tables['families_table']}
WHERE (husband = '{$personId}' or wife = '{$personId}') {$treeWhere}

SQL;
        $result = $this->query($sql);
        $row = $result->fetch_assoc();
        return $row;
    }

    /* Get Special events for ADMIN selection */

    function getEventList()
    {
        $sql = <<<SQL
    
SELECT *
FROM {$this->tables['eventtypes_table']}
ORDER BY display
	
SQL;
        $result = $this->query($sql);

        $rows = array();
        while ($row = $result->fetch_assoc()) {
            $eventrows[] = $row;
            //var_dump($eventrows);
        }

        return $eventrows;
    }

    /* Special event type 10 is called here */

    public function getSpEvent($personId = null, $tree = null)
    {

        if (!$personId) {
            $personId = $this->currentPerson;
        }
        $EventID = esc_attr(get_option('tng-api-tng-event'));
        $user = $this->getTngUser();
        $gedcom = $user['gedcom'];
        // If we are searching, enter $tree value
        if ($tree) {
            $gedcom = $tree;
        }
        $treeWhere = null;
        if ($gedcom) {
            $treeWhere = ' AND gedcom = "' . $gedcom . '"';
        }
        $sql = <<<SQL
		
SELECT *
FROM {$this->tables['events_table']}
where persfamID = '{$personId}' AND eventtypeID = '$EventID' {$treeWhere}
SQL;
        $result = $this->query($sql);
        $row = $result->fetch_assoc();

        return $row;
    }

    /* Display for Special event tng-event display is called here */

    public function getEventDisplay()
    {
        $EventID = esc_attr(get_option('tng-api-tng-event'));
        $sql = <<<SQL
		
SELECT *
FROM {$this->tables['eventtypes_table']}
where eventtypeID = "$EventID"
SQL;
        $result = $this->query($sql);
        $row = $result->fetch_assoc();

        return $row;
    }

// Special event type 0 for Cause of Death
    public function getCause($personId = null, $tree = null)
    {

        if (!$personId) {
            $personId = $this->currentPerson;
        }
        $user = $this->getTngUser();
        $gedcom = $user['gedcom'];
        // If we are searching, enter $tree value
        if ($tree) {
            $gedcom = $tree;
        }
        $treeWhere = null;
        if ($gedcom) {
            $treeWhere = ' AND gedcom = "' . $gedcom . '"';
        }
        $sql = <<<SQL
		
SELECT *
FROM {$this->tables['events_table']}
where persfamID = '{$personId}' AND eventtypeID = "o" AND parenttag = "DEAT" {$treeWhere}
SQL;
        $result = $this->query($sql);
        $row = $result->fetch_assoc();

        return $row;
    }

    public function getEvent($eventId, $tree = null)
    {
        $user = $this->getTngUser();
        $gedcom = $user['gedcom'];
        // If we are searching, enter $tree value
        if ($tree) {
            $gedcom = $tree;
        }
        $treeWhere = null;
        if ($gedcom) {
            $treeWhere = ' AND gedcom = "' . $gedcom . '"';
        }
        $sql = <<<SQL
		
SELECT *
FROM {$this->tables['events_table']}
where eventID = '{$eventId}' {$treeWhere}
SQL;
        $result = $this->query($sql);
        $row = $result->fetch_assoc();

        return $row;
    }

    public function getFamilyById($familyId = null, $tree = null)
    {
        $user = $this->getTngUser();
        $gedcom = $user['gedcom'];
        // If we are searching, enter $tree value
        if ($tree) {
            $gedcom = $tree;
        }
        $treeWhere = null;
        if ($gedcom) {
            $treeWhere = ' AND gedcom = "' . $gedcom . '" AND private = 0';
        }
        $sql = <<<SQL
SELECT *
FROM {$this->tables['families_table']}
WHERE familyID = '{$familyId}' {$treeWhere}
SQL;

        $result = $this->query($sql);
        $row = $result->fetch_assoc();

        return $row;
    }

    public function getChildFamily($personId, $familyId)
    {
        $user = $this->getTngUser();
        $gedcom = $user['gedcom'];
        // If we are searching, enter $tree value
        if ($tree) {
            $gedcom = $tree;
        }
        $treeWhere = null;
        if ($gedcom) {
            $treeWhere = ' AND gedcom = "' . $gedcom . '"';
        }

        $sql = <<<SQL
SELECT *
FROM {$this->tables['children_table']}
WHERE personID = '{$personId}' AND familyID = '{$familyId}' {$treeWhere}
SQL;

        $result = $this->query($sql);
        $row = $result->fetch_assoc();
        return $row;
    }

    public function getNotes($personId = null, $tree = null)
    {
        if (!$personId) {
            $personId = $this->currentPerson;
        }
        $user = $this->getTngUser();
        $gedcom = $user['gedcom'];
        // If we are searching, enter $tree value
        if ($tree) {
            $gedcom = $tree;
        }
        $treeWhere = null;
        if ($gedcom) {
            $treeWhere = ' AND n1.gedcom = "' . $gedcom . '"';
        }
        $sql = <<<SQL
SELECT nl.ID as notelinkID, nl.*, xl.*
FROM   {$this->tables['notelinks_table']} as nl
LEFT JOIN {$this->tables['xnotes_table']} AS xl
ON nl.xnoteID = xl.ID
where persfamID = '{$personId}'
SQL;
        $result = $this->query($sql);

        $rows = array();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function getDefaultMedia($personId = null, $tree = null)
    {

        if (!$personId) {
            $personId = $this->currentPerson;
        }
        $user = $this->getTngUser();
        $gedcom = $user['gedcom'];
        // If we are searching, enter $tree value
        if ($tree) {
            $gedcom = $tree;
        }
        $treeWhere = null;
        if ($gedcom) {
            $treeWhere = ' AND m.gedcom = "' . $gedcom . '"';
        }
        $sql = <<<SQL
		SELECT *
FROM   {$this->tables['medialinks_table']} as ml
    LEFT JOIN {$this->tables['media_table']} AS m
              ON ml.mediaID = m.mediaID
where personID = '{$personId}' AND defphoto = "1" 
SQL;
        $result = $this->query($sql);
        $row = $result->fetch_assoc();

        return $row;
    }

    public function getAllPersonMedia($personId = null, $tree = null)
    {

        if (!$personId) {
            $personId = $this->currentPerson;
        }
        $user = $this->getTngUser();
        $gedcom = $user['gedcom'];
        // If we are searching, enter $tree value
        if ($tree) {
            $gedcom = $tree;
        }
        $treeWhere = null;
        if ($gedcom) {
            $treeWhere = ' AND m.gedcom = "' . $gedcom . '"';
        }

        $sql = <<<SQL
SELECT *
FROM   {$this->tables['medialinks_table']} as ml
    LEFT JOIN {$this->tables['media_table']} AS m
              ON ml.mediaID = m.mediaID
where personID = '{$personId}' AND defphoto <> 1 {$treeWhere}
       
ORDER  BY ml.ordernum
          
SQL;
        $result = $this->query($sql);

        $rows = array();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function getProfileMedia($personId = null, $tree = null)
    {
        //get default media
        $defaultmedia = $this->getdefaultmedia($personId);
        //$mediaID = "../tng/photos/". $defaultmedia['thumbpath'];

        if ($defaultmedia['thumbpath'] == null AND $person['sex'] == "M") {
            $mediaID = "/img/male.jpg";
        }
        if ($defaultmedia['thumbpath'] == null AND $person['sex'] == "F") {
            $mediaID = "/img/female.jpg";
        }
        if ($defaultmedia['thumbpath'] !== null) {
            $mediaID = "/photos/" . $defaultmedia['thumbpath'];
        }
        return $this->getDomain() . $mediaID;
    }

    public function getChildren($familyId = null, $tree = nullS)
    {

        if (!$familyId) {
            return array();
        }
        $user = $this->getTngUser();
        $gedcom = $user['gedcom'];
        // If we are searching, enter $tree value
        if ($tree) {
            $gedcom = $tree;
        }
        $treeWhere = null;
        if ($gedcom) {
            $treeWhere = ' AND gedcom = "' . $gedcom . '"';
        }
        $sql = <<<SQL
	SELECT *
FROM {$this->tables['children_table']}
WHERE familyID = '{$familyId}' {$treeWhere}
ORDER BY ordernum 
SQL;
        $result = $this->query($sql);

        $rows = array();

        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        return $rows;
    }

    public function getChildrow($personId = null, $tree = null)
    {
        $user = $this->getTngUser();
        $gedcom = $user['gedcom'];
        // If we are searching, enter $tree value
        if ($tree) {
            $gedcom = $tree;
        }
        $treeWhere = null;
        if ($gedcom) {
            $treeWhere = ' AND gedcom = "' . $gedcom . '"';
        }
        $sql = <<<SQL
	SELECT *
FROM {$this->tables['children_table']}
WHERE personID = '{$personId}' {$treeWhere}
ORDER BY ordernum
SQL;
        $result = $this->query($sql);

        $rows = array();

        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        return $rows;
    }

    public function getFamilyUser($personId = null, $tree = null, $sortBy = null)
    {

        if (!$personId) {
            $personId = $this->currentPerson;
        }
        $user = $this->getTngUser();
        $gedcom = $user['gedcom'];
        // If we are searching, enter $tree value
        if ($tree) {
            $gedcom = $tree;
        }
        $treeWhere = null;
        if ($gedcom) {
            $treeWhere = ' AND gedcom = "' . $gedcom . '" AND private = 0';
        }

        $sql = <<<SQL
SELECT*
		
	
FROM {$this->tables['families_table']}

WHERE (husband = '{$personId}' {$treeWhere}) or (wife = '{$personId}' {$treeWhere})
SQL;
        $result = $this->query($sql);
        $rows = array();

        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        if ($sortBy) {
            $this->sortBy = $sortBy;
            usort($rows, array($this, 'sortRows'));
        }
        return $rows;
    }

    public function sortRows($a, $b)
    {
        if ($a[$this->sortBy] > $b[$this->sortBy]) {
            return 1;
        }
        if ($a[$this->sortBy] < $b[$this->sortBy]) {
            return -1;
        }
        return 0;
    }

    public function getBirthdays($month)
    {

        $user = $this->getTngUser();
        $gedcom = $user['gedcom'];
        // If we are searching, enter $tree value
        if ($tree) {
            $gedcom = $tree;
        }
        $treeWhere = null;
        if ($gedcom) {
            $treeWhere = ' AND gedcom = "' . $gedcom . '" AND private = 0';
        }

        $sql = <<<SQL
SELECT personid,
       firstname,
       lastname,
       birthdate,
       birthplace,
       gedcom
FROM   {$this->tables['people_table']}
WHERE  Month(birthdatetr) = {$month}
       AND living = 1 {$treeWhere}
ORDER  BY Day(birthdatetr),
          lastname
SQL;
        $result = $this->query($sql);

        $rows = array();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function getDeathAnniversaries($month)
    {
        $user = $this->getTngUser();
        $gedcom = $user['gedcom'];
        // If we are searching, enter $tree value
        if ($tree) {
            $gedcom = $tree;
        }
        $treeWhere = null;
        if ($gedcom) {
            $treeWhere = ' AND gedcom = "' . $gedcom . '" AND private = 0';
        }

        $sql = <<<SQL
SELECT personid,
       firstname,
       lastname,
       deathdate,
       deathplace,
       gedcom,
       Year(Now()) - Year(deathdatetr) AS Years
FROM   {$this->tables['people_table']}
WHERE  Month(deathdatetr) = {$month}
       AND living = 0 {$treeWhere}
ORDER  BY Day(deathdatetr),
          lastname
SQL;
        $result = $this->query($sql);

        $rows = array();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function getDeathAnniversariesPlusOne()
    {
        return $this->getDeathAnniversaries('MONTH(ADDDATE(now(), INTERVAL 1 month))');
    }

    public function getDeathAnniversariesPlusTwo($month)
    {
        $user = $this->getTngUser();
        $gedcom = $user['gedcom'];
        // If we are searching, enter $tree value
        if ($tree) {
            $gedcom = $tree;
        }
        $treeWhere = null;
        if ($gedcom) {
            $treeWhere = ' AND gedcom = "' . $gedcom . '" AND private = 0';
        }

        $sql = <<<SQL
SELECT personid,
       firstname,
       lastname,
       deathdate,
       deathplace,
       gedcom,
       Year(Now()) - Year(deathdatetr) AS Years
FROM   {$this->tables['people_table']}
WHERE  Month(deathdatetr) = MONTH(ADDDATE(now(), INTERVAL 2 month))
       AND living = 0 {$treeWhere}
ORDER  BY Day(deathdatetr),
          lastname
SQL;
        $result = $this->query($sql);

        $rows = array();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function getDeathAnniversariesPlusThree($month)
    {
        $user = $this->getTngUser();
        $gedcom = $user['gedcom'];
        // If we are searching, enter $tree value
        if ($tree) {
            $gedcom = $tree;
        }
        $treeWhere = null;
        if ($gedcom) {
            $treeWhere = ' AND gedcom = "' . $gedcom . '" AND private = 0';
        }

        $sql = <<<SQL
SELECT personid,
       firstname,
       lastname,
       deathdate,
       deathplace,
       gedcom,
       Year(Now()) - Year(deathdatetr) AS Years
FROM   {$this->tables['people_table']}
WHERE  Month(deathdatetr) = MONTH(ADDDATE(now(), INTERVAL 3 month))
       AND living = 0 {$treeWhere}
ORDER  BY Day(deathdatetr),
          lastname
SQL;
        $result = $this->query($sql);

        $rows = array();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function getMarriageAnniversaries($month)
    {
        $user = $this->getTngUser();
        $gedcom = $user['gedcom'];
        // If we are searching, enter $tree value
        if ($tree) {
            $gedcom = $tree;
        }
        $treeWhere = null;
        if ($gedcom) {
            $treeWhere = ' AND f.gedcom = "' . $gedcom . '" AND f.private = 0';
        }
        $sql = <<<SQL
SELECT
    h.gedcom,
    h.private,
    h.personid AS personid1,
    h.firstname AS firstname1,
    h.lastname AS lastname1,
    w.personid AS personid2,
    w.firstname AS firstname2,
    w.lastname AS lastname2,
    w.private,
    w.gedcom,
    f.gedcom,
    f.private,
    f.familyID,
    f.marrdate,
    f.marrplace,
    Year(Now()) - Year(marrdatetr) AS Years
FROM {$this->tables['families_table']} as f
    LEFT JOIN {$this->tables['people_table']} AS h
    ON (f.husband = h.personid AND f.gedcom = h.gedcom AND f.private = 0)
    LEFT JOIN {$this->tables['people_table']} AS w
    ON (f.wife = w.personid AND f.gedcom = w.gedcom AND w.private = 0)
WHERE  Month(f.marrdatetr) = {$month} 
{$treeWhere}
ORDER  BY Day(f.marrdatetr)
          
SQL;
        $result = $this->query($sql);

        $rows = array();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function searchPerson($searchFirstName, $searchLastName)
    {
        $wheres = array();
        if ($searchFirstName) {
            $wheres[] = "firstname LIKE '%{$searchFirstName}%'";
        }
        if ($searchLastName) {
            $wheres[] = "lastname LIKE '{$searchLastName}%'";
        }

        $rows = array();
        $where = null;
        if (count($wheres)) {
            $where = 'WHERE ' . implode(' AND ', $wheres);
        }

        $user = $this->getTngUser();
        $gedcom = $user['gedcom'];
        echo $user;
        if ($gedcom) {
            if (!$where) {
                $where = ' WHERE ';
            } else {
                $where .= ' AND ';
            }
            $where .= ' gedcom = "' . $gedcom . '" AND private = 0';
        }

        $sql = <<<SQL
SELECT *
FROM {$this->tables['people_table']}
{$where}
ORDER BY firstname, lastname
LIMIT 100
SQL;

        $result = $this->query($sql);

        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function getTngUserName()
    {
        $user = $this->getTngUser();
        return $user['username'];
    }

    public function getTngUser()
    {
        if ($this->tngUser) {
            return $this->tngUser;
        }
        $currentUser = wp_get_current_user();
        $userName = $currentUser->user_login;
        $query = "SELECT * FROM {$this->tables['users_table']} WHERE username='{$userName}'";
        $result = $this->query($query);
        $row = $result->fetch_assoc();
        if ($row) {
            $this->tngUser = $row;
            return $row;
        }
        wp_die('User ' . $userName . ' not found in TNG');
    }

    /**
     * @staticvar Upavadi_Repository_TngRepository $repo
     * @return \Upavadi_Repository_TngRepository
     */
    public function getRepo()
    {
        static $repo;
        if (!$repo) {
            $this->init();
            $repo = new Upavadi_Repository_TngRepository($this);
        }

        return $repo;
    }

    public function getTree()
    {
        $user = $this->getTngUser();
        return $user['gedcom'];
    }

}
?>
