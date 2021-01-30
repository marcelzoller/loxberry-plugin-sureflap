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
my %L = LoxBerry::Web::readlanguage($template, "language.ini");
  


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
if ($ENV{SERVER_PORT} != 80) {
	$server_port = ":".$ENV{SERVER_PORT};
}
$template->param( WEBSITE_GET => "http://$ENV{SERVER_NAME}$server_port/plugins/$lbpplugindir/getData.php");
$template->param( WEBSITE_SET1 => "http://$ENV{SERVER_NAME}$server_port/plugins/$lbpplugindir/setPetLocation.php");
$template->param( WEBSITE_SET2 => "http://$ENV{SERVER_NAME}$server_port/plugins/$lbpplugindir/setLockMode.php");
$template->param( WEBSITE_SET3 => "http://$ENV{SERVER_NAME}$server_port/plugins/$lbpplugindir/setHubLedBrightness.php");
$template->param( WEBSITE_SET4 => "http://$ENV{SERVER_NAME}$server_port/plugins/$lbpplugindir/setEnableCurfew.php");
$template->param( LOGDATEI => "/admin/system/tools/logfile.cgi?logfile=$lbplogdir/sureflap.log&header=html&format=template");
#$template->param( WEBSTATUS => "http://$ENV{SERVER_NAME}:$server_port/plugins/$lbpplugindir/status.cgi");  
 if ($HTTPSEND == 1) {
	$template->param( HTTPSENDYES => "selected=selected");
	$template->param( HTTPSENDNO => "");
} else {
	$template->param( HTTPSENDYES => "");
	$template->param( HTTPSENDNO => "selected=selected");
}

  
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
			# tied(%pcfg)->write();
		} 
	if ($R::PASSWORD ne "") {
			LOGDEB "Password: $R::PASSWORD";
			$pcfg{'MAIN.PASSWORD'} = $R::PASSWORD;
			# tied(%pcfg)->write();
		} 
	if ($R::MINISERVER ne "") {
			$pcfg{'MAIN.MINISERVER'} = $R::MINISERVER;
			# tied(%pcfg)->write();
		}
	if ($R::HTTPSEND == "1") {
			#LOGDEB "HTTP Send: $R::HTTP_TEXT_Send";
			$pcfg{'MAIN.HTTPSEND'} = "1";
			# tied(%pcfg)->write();
	} else{
			#LOGDEB "HTTP Send: $R::HTTP_TEXT_Send";
			$pcfg{'MAIN.HTTPSEND'} = "0";
			# tied(%pcfg)->write();
	}
		
	tied(%pcfg)->write();
	LOGDEB "---------- Setting: End Save ------------";
	#	print "SAVE!!!!";	
	return;
	
}

