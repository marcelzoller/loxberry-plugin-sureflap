#!/usr/bin/perl


##########################################################################
# LoxBerry-Module
##########################################################################
use CGI;
use LoxBerry::System;
use LoxBerry::Web;
use LoxBerry::Log;
  
# Die Version des Plugins wird direkt aus der Plugin-Datenbank gelesen.
my $version = LoxBerry::System::pluginversion();

# Loxone Miniserver Select Liste Variable
our $MSselectlist;

# Mit dieser Konstruktion lesen wir uns alle POST-Parameter in den Namespace R.
my $cgi = CGI->new;
$cgi->import_names('R');
# Ab jetzt kann beispielsweise ein POST-Parameter 'form' ausgelesen werden mit $R::form.

# Create my logging object
my $log = LoxBerry::Log->new ( 
	name => 'HTTP Admin Settup',
	filename => "$lbplogdir/sureflap.log",
	append => 1
	);
LOGSTART "SureFlap HTTP Admin start";

our $htmlhead = "<script src='/admin/plugins/sureflap/pw.js'></script>";
 
# Wir Übergeben die Titelzeile (mit Versionsnummer), einen Link ins Wiki und das Hilfe-Template.
# Um die Sprache der Hilfe brauchen wir uns im Code nicht weiter zu kümmern.
LoxBerry::Web::lbheader("SureFlap Connect Plugin V$version", "http://www.loxwiki.eu/SureFlap/Zoller", "help.html");
  
# Wir holen uns die Plugin-Config in den Hash %pcfg. Damit kannst du die Parameter mit $pcfg{'Section.Label'} direkt auslesen.
my %pcfg;
tie %pcfg, "Config::Simple", "$lbpconfigdir/pluginconfig.cfg";

# Alle Miniserver aus Loxberry config auslesen
%miniservers = LoxBerry::System::get_miniservers();


# Wir initialisieren unser Template. Der Pfad zum Templateverzeichnis steht in der globalen Variable $lbptemplatedir.

my $template = HTML::Template->new(
    filename => "$lbptemplatedir/index.html",
    global_vars => 1,
    loop_context_vars => 1,
    die_on_bad_params => 0,
	associate => $cgi,
);
  

# Sprachdatei laden
my %L = LoxBerry::System::readlanguage($template, "language.ini");
  


##########################################################################
# Process form data
##########################################################################
if ($cgi->param("save")) {
	# Data were posted - save 
	&save;
}
	
my $EMAIL = %pcfg{'MAIN.EMAIL'};
my $PASSWORD = %pcfg{'MAIN.PASSWORD'};
my $MINISERVER = %pcfg{'MAIN.MINISERVER'};
my $HTTPSEND = %pcfg{'MAIN.HTTPSEND'};
my $MQTTSEND = %pcfg{'MAIN.MQTTSEND'};
my $MQTT_TOPIC = %pcfg{'MAIN.MQTT_TOPIC'};

##########################################################################
# Fill Miniserver selection dropdown
##########################################################################
for (my $i = 1; $i <=  keys(%miniservers);$i++) {
	if ($miniservers{$i}{Name} eq $MINISERVER) {
		$MSselectlist .= '<option selected value="'.$miniservers{$i}{Name}.'">'.$miniservers{$i}{Name}."</option>\n";
	} else {
		$MSselectlist .= '<option value="'.$miniservers{$i}{Name}.'">'.$miniservers{$i}{Name}."</option>\n";
	}
}

$template->param( EMAIL => $EMAIL);
$template->param( PASSWORD => $PASSWORD);
$template->param( LOXLIST => $MSselectlist);
$server_protocol = "http";
if (lc($ENV{HTTPS}) eq "on") {
	$server_protocol = "https";
}
if ($ENV{SERVER_PORT} != 80 and $ENV{SERVER_PORT} != 443) {
	$server_port = ":".$ENV{SERVER_PORT};
}
$server_path = "$server_protocol://$ENV{SERVER_NAME}$server_port/plugins/$lbpplugindir";

$template->param( WEBSITE_GET  => "$server_path/getData.php");
$template->param( WEBSITE_SET1 => "$server_path/setPetLocation.php");
$template->param( WEBSITE_SET2 => "$server_path/setPetLocking.php");
$template->param( WEBSITE_SET3 => "$server_path/setLockMode.php");
$template->param( WEBSITE_SET4 => "$server_path/setHubLedBrightness.php");
$template->param( WEBSITE_SET5 => "$server_path/setCurfew.php");
$template->param( LOGDATEI => "/admin/system/tools/logfile.cgi?logfile=$lbplogdir/sureflap.log&header=html&format=template");
#$template->param( WEBSTATUS => "http://$ENV{SERVER_NAME}:$server_port/plugins/$lbpplugindir/status.cgi");  
 if ($HTTPSEND == 1) {
	$template->param( HTTPSENDYES => "selected=selected");
	$template->param( HTTPSENDNO => "");
} else {
	$template->param( HTTPSENDYES => "");
	$template->param( HTTPSENDNO => "selected=selected");
}
 if ($MQTTSEND == 1) {
	$template->param( MQTTSENDYES => "selected=selected");
	$template->param( MQTTSENDNO => "");
} else {
	$template->param( MQTTSENDYES => "");
	$template->param( MQTTSENDNO => "selected=selected");
}
$template->param( MQTT_TOPIC => $MQTT_TOPIC);

  
# Nun wird das Template ausgegeben.
print $template->output();
  
# Schlussendlich lassen wir noch den Footer ausgeben.
LoxBerry::Web::lbfooter();

LOGEND "SureFlap Admin Setting finish.";

##########################################################################
# Save data
##########################################################################
sub save 
{

	# We import all variables to the R (=result) namespace
	$cgi->import_names('R');
	
	LOGDEB "---------- Setting: Start Save ------------";
	
	if ($R::EMAIL ne "") {
		LOGDEB "EMail: $R::EMAIL";
		$pcfg{'MAIN.EMAIL'} = $R::EMAIL;
	} 
	if ($R::PASSWORD ne "") {
		LOGDEB "Password: $R::PASSWORD";
		$pcfg{'MAIN.PASSWORD'} = $R::PASSWORD;
	} 
	if ($R::MINISERVER ne "") {
		LOGDEB "Miniserver: $R::MINISERVER";
		$pcfg{'MAIN.MINISERVER'} = $R::MINISERVER;
	}
	if ($R::HTTPSEND == "1") {
		$pcfg{'MAIN.HTTPSEND'} = "1";
	} else{
		$pcfg{'MAIN.HTTPSEND'} = "0";
	}
	LOGDEB "HTTPSEND: $R::HTTPSEND";
	if ($R::MQTTSEND == "1") {
		$pcfg{'MAIN.MQTTSEND'} = "1";
	} else{
		$pcfg{'MAIN.MQTTSEND'} = "0";
	}
	LOGDEB "MQTTSEND: $R::MQTTSEND";
	if ($R::MQTT_TOPIC ne "") {
		LOGDEB "MQTT Topic: $R::MQTT_TOPIC";
		$pcfg{'MAIN.MQTT_TOPIC'} = $R::MQTT_TOPIC;
	}
		
	tied(%pcfg)->write();
	LOGDEB "---------- Setting: End Save ------------";
	#	print "SAVE!!!!";	
	return;
	
}

