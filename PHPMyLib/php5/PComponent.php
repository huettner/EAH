<?php
/**
* PHPMyLib library for creation and handling of html-objects (component of PPage)
*
* @author i-fabrik GmbH
* @copyright 2002 by i-fabrik GmbH
* @version $Id: PComponent.php,v 1.16 2006/06/06 09:20:00 ralph Exp $
*
*/

//if ( @is_readable(PML_PATH.'PObject.php') ) require_once(PML_PATH.'PObject.php');
//    else require_once('PHPMyLib/PObject.php');

/**
* Konstanten f�r die �bergabe von Parametern an Funktionen
*/
if ( !defined('PTEXT_NOCHANGE') )              define('PTEXT_NOCHANGE',               'nochange');
if ( !defined('PTEXT_NL2BR') )                 define('PTEXT_NL2BR',                     'nl2br');
if ( !defined('PTEXT_HPEDITOR') )              define('PTEXT_HPEDITOR',               'hpEditor');
if ( !defined('PTEXT_REMOVE_HPEDITOR') )       define('PTEXT_REMOVE_HPEDITOR', 'remove_hpEditor');

if ( !defined('PSELECT_NO_DUPLICATES') )       define('PSELECT_NO_DUPLICATES',    1);
if ( !defined('PSELECT_DUPLICATE_VALUES') )    define('PSELECT_DUPLICATE_VALUES', 2);
if ( !defined('PSELECT_DUPLICATE_NAMES') )     define('PSELECT_DUPLICATE_NAMES',  3);
if ( !defined('PSELECT_DUPLICATE_BOTH') )      define('PSELECT_DUPLICATE_BOTH',   4);

if ( !defined('PTAG_NEW_LINE') )               define('PTAG_NEW_LINE',     TRUE);
if ( !defined('PTAG_NO_NEW_LINE') )            define('PTAG_NO_NEW_LINE', FALSE);

if ( !defined('PTABLELAYOUT_ALTERNATE_ROWS') ) define('PTABLELAYOUT_ALTERNATE_ROWS',  TRUE);
if ( !defined('PTABLELAYOUT_SPECIAL_ROWS') )   define('PTABLELAYOUT_SPECIAL_ROWS',   FALSE);


/**
* Die Klasse PComponent ist die Basisklasse aller Klassen, die HTML-Ausgaben erzeugen. Deshalb
* besitzt diese Klasse eine Methode outputStr(), die HTML-Text erzeugt. Im folgenden werden solche
* Objekte als Komponenten bezeichnet.
**/
class PComponent extends PObject {

    /**
    * Titel der Komponente, der i.a. nicht angezeigt wird.
    **/
    var $m_strText;

    /**
    * Konstruktor der PComponent-Klasse
    * <b>Parameter:</b>
    * keine
    **/
    function PComponent($string='') {
        $this->setText($string);
    } //Constructor: PComponent

    /**
    * setText setzt den Titel der Komponente
    * <b>Parameter:</b>
    * $text - Der neue Text f�r den Titel
    * <b>R�ckgabe:</b>
    * keine
    **/
    function setText($text = '') {
        if ( !@is_array($text) && !@is_object($text)) $this->m_strText = (string)$text;
    }

    /**
    * Liefert den aktuellen Titel der Komponente.
    * <b>Parameter:</b>
    * keine
    * <b>R�ckgabe:</b>
    * Titel der Komponente.
    **/
    function getText() {
        return ($this->m_strText);
    }


    /**
    * Gibt den Inhalt der Komponente als HTML zur�ck
    *
    * <b>Parameter:</b>
    * keine
    *
    * <b>R�ckgabe:</b>
    * Der Inhalt der Komponente als string.
    **/
    function outputStr() {
       die("\nFatal Error: You have to implement outputStr() in ".$this->getClassName());
    }
} //class PComponent


/**
* Die Klasse PContainer ist in der Lage weiter Komponenten in sich aufzunehmen.
* Dadurch ist es m�glich komplizierte HTML-Inhalte als eine Komponente darzustellen.
* Zu beachten ist, dass bei PTag-Objekte, die eingef�gt werden jeweils das schlie�ende Tag
* an das Ende aller anderen Objekte angeh�ngt werden.
**/
class PContainer extends PComponent {
    /**
    * Array der enthaltenen Komponenten
    **/
    var $m_aChildren;
    /**
    * Anzahl der enthaltenen Komponenten
    **/
    var $m_nChildCount;

    /**
    * Konstruktor der PContainer-Klasse
    * <b>Parameter:</b>
    * optionale Komponente, welche dem Objekt mit der Funktion add() hinzugef�gt wird
    **/
    function PContainer( $component=FALSE ) {
       $this->PComponent();
       $this->m_aChildren   = array();
       $this->m_nChildCount = 0;
        if ( $component !== FALSE ) $this->add($component);
    }

    /**
    * Diese Methode f�gt eine Komponente hinzu.
    * <b>Parameter</b>
    * $component - Die Komponente die hinzugef�gt werden soll
    *				( oder Array mit den Komponenten, die hinzugef�gt werden sollen )
    * <b>R�ckgabe</b>
    * Der Index der letzten neu eingef�gten Komponente. Im Fehlerfall -1
    **/
    function add($component) {
        if ( $this->isPComponent($component) ) {
            $this->m_aChildren[$this->m_nChildCount] = clone $component;
            $this->m_nChildCount++;
            return ($this->m_nChildCount-1);
        } elseif ( @is_array($component) ) foreach ($component as $comp) $return = $this->add($comp);
        if ( isset($return) ) return $return;
            else return (-1);
    }

    /**
    * Diese Methode l�scht eine Komponente. Die Indizes aller anderen dahinterliegenden Komponenten verschieben sich.
    * <b>Parameter</b>
    * $index - Index der zu l�schenden Komponente.
    * <b>R�ckgabe</b>
    * Der Index der neu eingef�gten Komponente. Im Fehlerfall -1
    **/
    function remove($index) {
        if ($this->m_nChildCount > $index && $index >= 0) {
            array_splice($this->m_aChildren, $index, 1);
            $this->m_nChildCount--;
        }
    }
    // remove

    /**
    * Die Methode pr�ft, ob es sich bei $obj um eine von PContainer abgeleitete Klasse handelt,
    * oder um eine Klasse des Typs PContainer selbst
    *
    * <b>Parameter:</b>
    * $obj - Die Instanz einer Klasse, die �berpr�ft werden soll
    *
    * <b>R�ckgabe:</b>
    * true oder false, je nach Ergebnis der �berpr�fung.
    **/
    function isPContainer($obj) {
        if ( $this->isPObject($obj) && isset($obj->m_aChildren) ) return TRUE;
            else return FALSE;
    }
    // isPContainer

    /**
    * Die Methode pr�ft, ob es sich bei $obj um eine von PTag abgeleitete Klasse handelt,
    * oder um eine Klasse des Typs PTag selbst
    *
    * <b>Parameter:</b>
    * $obj - Die Instanz einer Klasse, die �berpr�ft werden soll
    *
    * <b>R�ckgabe:</b>
    * true oder false, je nach Ergebnis der �berpr�fung.
    **/
    function isPTag($obj) {
       if ( $this->isPObject($obj) && isset($obj->m_aParams) ) return TRUE;
        else return FALSE;
    }

    /**
    * Gibt den Inhalt des Containers als HTML zur�ck. Es werden alle enthaltenen Komponenten ausgegeben.
    *
    * <b>Parameter:</b>
    * keine
    *
    * <b>R�ckgabe:</b>
    * Der Inhalt des Containers als string.
    **/
    function outputStr() {
        $str='';
        for($i=0;$i<$this->m_nChildCount;$i++)		$str.=$this->m_aChildren[$i]->outputStr();
        for($i=$this->m_nChildCount-1;$i>=0;$i--)	if ( $this->isPTag($this->m_aChildren[$i]) ) $str.=$this->m_aChildren[$i]->outputEndStr();
        return $str;
    }

} //class PContainer

/**
* Die Klasse PTag ist die Wrapper-Klasse f�r beliebige HTML-Tags. Sie ist in der Lage �ffnende
* und schlie�ende Tags, sowie Parameter zu verwalten.
**/
class PTag extends PComponent {

    /**
    * Array der Parameter des Tags
    **/
    var $m_aParams;
    /**
    * Liste der Tags, die keine schlie�enden Tag ben�tigen
    **/
    var $m_aSingle;

    /**
    * Konstruktor der PTag-Klasse. Erzeugt ein PTag-Objekt des Typs $name mit den Parametern $params
    * <b>Parameter:</b>
    * $name - Das Tag ohne "<" oder ">"
    * $params - Array der Parameter mit key=>value-Struktur
    **/
    function PTag($name = 'p', $params = array(), $nl = PTAG_NEW_LINE) {
        $this->PComponent();
        $this->m_newLine = $nl;
        $this->m_strText = strtolower($name);
        if ( @is_array($params) ) $this->m_aParams = $params; else $this->m_aParams = array();
        $this->m_aSingle = array('input', 'br', 'img', );
    }

    /**
    * Die Methode f�gt weiter Parameter den bereits bestehenden hinzu.
    * <b>Parameter:</b>
    * $params - Array der hinzuzuf�genden Parameter (key=>value - Struktur)
    **/
    function addParam($params) {
        if ( is_array($params) ) $this->m_aParams = array_merge($this->m_aParams, $params);
    }

    /**
    * Die Methode setzt einen Parameter im Tag. Wenn dieser bereits vorhanden ist
    * wird er �berschrieben.
    * <b>Parameter:</b>
    * $name - Name des Parameters
    * $value - Wert des Parameters
    **/
    function setParam($name, $value) {
        if ( !empty($name) && is_string($name) ) {
            $this->m_aParams[$name] = $value;
            return TRUE;
        } else return FALSE;
    }

    /**
    * �berladene Methode von PComponent. Gibt das �ffnende Tag als String aus.
    * <b>Parameter:</b>
    * keine
    * <b>R�ckgabe:</b>
    * Das �ffnende Tag
    **/
    function outputStr() {
        $text = '';
        if ( $this->m_newLine ) $text .= "\n";
        $text .= '<'.$this->m_strText;
        foreach ( $this->m_aParams as $param => $val ) {
            if ( $val !== NULL ) $text .= ' '.$param.'="'.$val.'"';
                else $text.=' '.$param.'="'.$param.'"';
        }
        if ( in_array($this->m_strText, $this->m_aSingle) ) $text .= ' /';
        $text .= '>';
        return $text;
    }

    /**
    * Diese Methode gibt, sofern das Tag dies erlaubt das schlie�ende Tag als String aus.
    * <b>Parameter:</b>
    * keine
    * <b>R�ckgabe:</b>
    * Das schlie�ende Tag
    **/
    function outputEndStr() {
        if ( !in_array($this->m_strText, $this->m_aSingle) ) return ('</'.$this->m_strText.'>');
        return '';
    }
}
// class PTag

/**
* Diese Klasse ist die einfachste abgeleitete Klasse von PComponent. Sie gibt lediglich
* den in ihr enthaltenen Text aus.
**/
class PText extends PComponent {

    /**
    * Ausgabeformatierung:
    * 0 - HTML-Tags bleiben unver�ndert
    * 1 - alle HTML-Tags entfernen
    **/
    var $removeHTML;

    /**
    * Ausgabeformatierung, erlaubte Konstanten:
    * PTEXT_NOCHANGE - keine Ver�nderung am Text
    * PTEXT_NL2BR - wandelt ASCII-Zeilenumbr�che in BR-Tags (empfohlen wenn HTML-Tags entfernt werden)
    * PTEXT_HPEDITOR - wandelt Formatierung des js-Editors der ifabrik in HTML
    * PTEXT_REMOVE_HPEDITOR - entfernt Formatierung des js-Editors der ifabrik
    **/
    var $outputFormat;

    /**
    * Konstruktor der PText Klasse
    * <b>Parameter:</b>
    * $text - Der Text der ausgegeben werden soll
    * $stripHTML - bestimmt, wie HTML-Tags behandelt werden
    * FALSE - HTML-Tags bleiben unver�ndert (default)
    * TRUE - alle HTML-Tags werden entfernt
    * $format - bestimmt wie der gespeicherte Text formatiert werden soll, folgende Konstanten sind erlaubt
    * PTEXT_NOCHANGE - Text unver�ndert ausgeben
    * PTEXT_NL2BR - wandelt ASCII-Zeilenumbr�che in BR-Tags (empfohlen wenn HTML-Tags entfernt werden)
    * PTEXT_HPEDITOR - wandelt Formatierung des js-Editors der ifabrik in HTML
    * PTEXT_REMOVE_HPEDITOR - entfernt Formatierung des js-Editors der ifabrik
    * $maxLineLength - maximale L�nge einer Zeile (danach wird Zeilenumbruch eingef�gt, 0=unbegrenzt)
    **/
    function PText($text, $html=0, $format=PTEXT_NOCHANGE, $maxLineLength=0) {
        $this->PComponent();
        $this->removeHTML=$html;
        $this->outputFormat=$format;
        $this->setText($text);
        $this->maxLineLength=$maxLineLength;
    }
    // PText

    /**
    * �berladene Methode von PComponent. Gibt den Text aus.
    * <b>Parameter:</b>
    * keine
    * <b>R�ckgabe:</b>
    * Der Text als String
    **/
    function outputStr() {

        switch ( (int)$this->removeHTML ):
        case 1:
            $text=@strip_tags($this->m_strText);
            break;
        case 2:
            $text=$this->m_strText;
            $text=@preg_replace("/<br( \/)?(>)/i", "\n", $text);
            $text=@preg_replace("/<\/?p[^>]*>/i", "\n", $text);
            $text=@strip_tags($text);
            break;
        default:
            $text=$this->m_strText;
            break;
        endswitch;



        switch ($this->outputFormat):
        case PTEXT_NL2BR:
            $text=@nl2br($text);
            break;

        case PTEXT_HPEDITOR:
            // tag replacement for javascript editor (before v3.2)
            $text = @preg_replace("/\[BOLD\](.*)\[\/BOLD\]/siU","<b>\\1</b>",$text);												// fetter Text
            $text = @preg_replace("/\[ITALIC\](.*)\[\/ITALIC\]/siU","<i>\\1</i>",$text);											// kursiver Text
            $text = @preg_replace("/\[UNDERLINE\](.*)\[\/UNDERLINE\]/siU","<u>\\1</u>",$text);									// unterstrichener text
            $text = @preg_replace("/\[HEADING=(.*)\](.*)\[\/HEADING\]/siU","<h\\1>\\2</h\\1>",$text);							// �berschrift
            $text = @preg_replace("/\[STYLE=(.*)\](.*)\[\/STYLE\]/siU","<span class=\"\\1\">\\2</span>",$text);			// CSS Style
            $text = @preg_replace("/\[URL\=(.*)\](.*)\[\/URL\]/siU","<a href=\"\\1\" target=\"_new\">\\2</a>",$text);	// URL Link
            $text = @preg_replace("/\[EMAIL=(.*)\](.*)\[\/EMAIL\]/siU","<a href=\"mailto:\\1\">\\2</a>",$text);			// E-Mail Link
            $text = @preg_replace("/\[FONTNAME=(.*)\](.*)\[\/FONTNAME\]/siU","<font face=\"\\1\">\\2</font>",$text);		// Schriftart
            $text = @preg_replace("/\[FONTSIZE=(.*)\](.*)\[\/FONTSIZE\]/siU","<font size=\"\\1\">\\2</font>",$text);		// Schriftgr�sse
            while ( @preg_match("/\[LIST=(.*)\]/siU", $text, $matches) ) {																// Listen
                if ( $matches[1] == 'u' ) $text = preg_replace("/\[LIST=u\](.*)\[\/LIST\]/siU","<ul>\\1</ul>",$text);
                    elseif ( $matches[1 ] == 'o') $text = preg_replace("/\[LIST=o\](.*)\[\/LIST\]/siU","<ol>\\1</ol>",$text);
                        else $text = preg_replace("/\[LIST=(.*)\](.*)\[\/LIST\]/siU","<ol type=\"\\1\">\\2</ol>",$text);
                $text = str_replace('[ITEM]', '<li>', $text);
                $text = str_replace('[/ITEM]', '</li>', $text);
            }


            // tag replacement for javascript editor (v3.2, and above?)
            $text = @preg_replace("/\[b\](.*)\[\/b\]/siU", "<b>\\1</b>", $text);											// fetter Text
            $text = @preg_replace("/\[i\](.*)\[\/i\]/siU", "<i>\\1</i>", $text);											// kursiver Text
            $text = @preg_replace("/\[u\](.*)\[\/u\]/siU", "<u>\\1</u>", $text);											// unterstrichener text
            $text = @preg_replace("/\[pol\](.*)\[\/pol\]/siU", "<span align=\"left\">\\1</span>", $text);		// linksbuendiger text
            $text = @preg_replace("/\[por\](.*)\[\/por\]/siU", "<span align=\"right\">\\1</span>", $text);		// rechtsbuendiger text
            $text = @preg_replace("/\[poc\](.*)\[\/poc\]/siU", "<span align=\"center\">\\1</span>", $text);		// zentrierter text
            $text = @preg_replace("/\[w=([^\]]*)\](.*)\[\/w\]/siU", "<a href=\"\\1\">\\2</a>", $text);			// Url
            $text = @preg_replace("/\[e=([^\]]*)\](.*)\[\/e\]/siU", "<a href=\"mailto:\\1\">\\2</a>", $text);	// E-Mail
            $text = @preg_replace("/\[p=([^\]]*)\]/siU", "<img src=\\1>", $text);										// Bild
            $text = @preg_replace("/\[l=([^\]]*)\]/siU", "<hr size=\"\\1\">", $text);									// Linie
            $text = @preg_replace("/\[c=([^\]]*)\](.*)\[\/c\]/siU", "<span class=\"\\1\">\\2</span>", $text);	// css-Klasse
            $text = @preg_replace("/\[fs=([^\]]*)\](.*)\[\/fs\]/siU", "<font size=\"\\1\">\\2</font>", $text);	// Schriftgroesse
            $text = @preg_replace("/\[fn=([^\]]*)\](.*)\[\/fn\]/siU", "<font face=\"\\1\">\\2</font>", $text);	// Schriftart
            $text = @preg_replace("/\[h=([^\]]*)\](.*)\[\/h\]/siU", "<h\\1>\\2</h\\1>", $text);						// Ueberschrift
            $text = @preg_replace("/\[po=([^\]]*)\](.*)\[\/po\]/siU", "<span align=\"\\1\">\\2</span>", $text);// Position
            while ( @preg_match("/\[li=(.*)\]/siU", $text, $matches) ) {													// Listen
                if ( $matches[1] == 'u' ) $text = preg_replace("/\[li=u\](.*)\[\/li\]/siU","<ul>\\1</ul>",$text);
                    elseif ( $matches[1 ] == 'o') $text = preg_replace("/\[li=o\](.*)\[\/li\]/siU","<ol>\\1</ol>",$text);
                        else $text = preg_replace("/\[li=(.*)\](.*)\[\/li\]/siU","<ol type=\"\\1\">\\2</ol>",$text);
                $text = preg_replace("/\[(\/)?it\]/siU", "<\\1li>", $text);
            }

            $pattern = array('<ul><br />','</ul><br />','<ol><br />','</ol><br />','<ol type="a"><br />','<ol type="A"><br />','<ol type="i"><br />','<ol type="I"><br />','</li><br />');
            $replacePattern = array("<ul>\n","</ul>\n","<ol>\n","</ol>\n","<ol type=\"a\">\n","<ol type=\"A\">\n","<ol type=\"i\">\n","<ol type=\"I\">\n","</li>\n");
            $text=str_replace($pattern, $replacePattern, @nl2br($text));
            break;

        case PTEXT_REMOVE_HPEDITOR:
            // remove tags from older javascript editor (before v3.2)
            $text = @preg_replace("/\[BOLD\](.*)\[\/BOLD\]/siU", "\\1", $text);								// fetter Text
            $text = @preg_replace("/\[ITALIC\](.*)\[\/ITALIC\]/siU", "\\1", $text);							// kursiver Text
            $text = @preg_replace("/\[UNDERLINE\](.*)\[\/UNDERLINE\]/siU", "\\1", $text);					// unterstrichener text
            $text = @preg_replace("/\[HEADING=(.*)\](.*)\[\/HEADING\]/siU", "\\2", $text);				// �berschrift
            $text = @preg_replace("/\[STYLE=(.*)\](.*)\[\/STYLE\]/siU", "\\2", $text);						// CSS Style
            $text = @preg_replace("/\[URL\=(.*)\](.*)\[\/URL\]/siU", "\\2 (\\1)", $text);					// URL Link
            $text = @preg_replace("/\[EMAIL=(.*)\](.*)\[\/EMAIL\]/siU", "\\2 (mailto:\\1)", $text);	// E-Mail Link
            $text = @preg_replace("/\[FONTNAME=(.*)\](.*)\[\/FONTNAME\]/siU", "\\2", $text);				// Schriftart
            $text = @preg_replace("/\[FONTSIZE=(.*)\](.*)\[\/FONTSIZE\]/siU", "\\2", $text);				// Schriftgroesse
            $text = @preg_replace("/\[\/ITEM\](\s*?)\[ITEM\]/siU", ", \r\n", $text);						// Seperator zw. Listenelementen
            $text = @preg_replace("/\[LIST=(.*)\](.*)\[\/LIST\]/siU", "\\2", $text);						// Listenanfang/-ende
            $text = @preg_replace("/\[\/?ITEM\]/siU", '', $text);													// restliche Listenelemente

            // remove tags from javascript editor (v3.2, and above?)
            $text = @preg_replace("/\[b\](.*)\[\/b\]/siU", "\\1", $text);										// fetter Text
            $text = @preg_replace("/\[i\](.*)\[\/i\]/siU", "\\1", $text);										// kursiver Text
            $text = @preg_replace("/\[u\](.*)\[\/u\]/siU", "\\1", $text);										// unterstrichener text
            $text = @preg_replace("/\[pol\](.*)\[\/pol\]/siU", "\\1", $text);									// linksbuendiger text
            $text = @preg_replace("/\[por\](.*)\[\/por\]/siU", "\\1", $text);									// rechtsbuendiger text
            $text = @preg_replace("/\[poc\](.*)\[\/poc\]/siU", "\\1", $text);									// zentrierter text
            $text = @preg_replace("/\[w=([^\]]*)\](.*?)\[\/w\]/siU", "\\2 (\\1)", $text);								// Url
            $text = @preg_replace("/\[e=[^\]]*\](.*)\[\/e\]/siU", "\\1", $text);								// E-Mail
            $text = @preg_replace("/\[p=[^\]]*\]/siU", '', $text);												// Bild
            $text = @preg_replace("/\[l=[^\]]*\]/siU", '', $text);												// Linie
            $text = @preg_replace("/\[c=[^\]]*\](.*)\[\/c\]/siU", "\\1", $text);								// css-Klasse
            $text = @preg_replace("/\[fs=[^\]]*\](.*)\[\/fs\]/siU", "\\1", $text);							// Schriftgroesse
            $text = @preg_replace("/\[fn=[^\]]*\](.*)\[\/fn\]/siU", "\\1", $text);							// Schriftart
            $text = @preg_replace("/\[h=[^\]]*\](.*)\[\/h\]/siU", "\\1", $text);								// Ueberschrift
            $text = @preg_replace("/\[po=[^\]]*\](.*)\[\/po\]/siU", "\\1", $text);							// Position
            $text = @preg_replace("/\[\/it\](\s*?)\[it\]/siU", ", \r\n", $text);								// Seperator zw. Listenelementen
            $text = @preg_replace("/\[li=[^\]]*\](.*)\[\/li\]/siU", "\\1", $text);							// Listenanfang/-ende
            $text = @preg_replace("/\[\/?it\]/siU", '', $text);													// restliche Listenelemente
            break;

        case PTEXT_NOCHANGE:
        default:
            $text=$this->m_strText;
            break;
        endswitch;

        if ( $this->maxLineLength > 0 ) {
            if ( strip_tags($text)!=$text ) $text=PUtil::wordwrap_html($text, $this->maxLineLength, " <br />\n");
                else $text=wordwrap($text, $this->maxLineLength, '\n', 1);
        }

        return $text;
    }
    // outputStr
}
// class PText


/**
* Die Klasse PList ist die Wrapper-Klasse f�r das HTML-Listen-Objekt (<ul>). S�mtliche
* Eintr�ge in der Liste werden von der Klasse verwaltet. Da PList von PConatiner abgeleitet
* ist, wird die Methode add() zum Hinzuf�gen von Listen-Eintr�gen genutzt.
**/
class PList extends PContainer
{
   var $m_nBullet;

   /**
   * Konstruktor der PListe Klasse
   * <b>Parameter:</b>
   * $bullet - Typ des Bullets: 0 Punkt, 1 Quadrat, 2 Kreis, 3 keine
   **/
   function PList($bullet = 0) {
    $this->PContainer();
    $this->m_nBullet=(int)$bullet;
   }

    /**
    * �berladene Methode von PComponent. Gibt die Liste samt aller Eintr�ge aus.
    * <b>Parameter:</b>
    * keine
    * <b>R�ckgabe:</b>
    * Die Liste als String (HTML)
    **/
    function outputStr() {
        $tags    = '';
        $tagsEnd = '';

        for ($i=0;$i<$this->m_nChildCount;$i++) {
            if ( $this->isPTag($this->m_aChildren[$i]) ) {
                $tags    .= $this->m_aChildren[$i]->outputStr();
                $tagsEnd .= $this->m_aChildren[$i]->outputEndStr();
            }
        }

        switch ( $this->m_nBullet ) {
            default:
            case 0: $tmp="type=disc"; break;
            case 1: $tmp="type=square"; break;
            case 2: $tmp="type=circle"; break;
            case 3: $tmp="compact"; break;
        }
        $str="\n<ul $tmp>";
        for ($i=0;$i<$this->m_nChildCount;$i++) {
            if ( !$this->isPTag($this->m_aChildren[$i]) ) {
                $str.="\n\t<li>".$tags;
                $str.=$this->m_aChildren[$i]->outputStr().$tagsEnd."</li>";
            }
        }
        return ($str."\n</ul>");
    }
}
//class PList

/**
* PForm ist die Wrapper-Klasse f�r Formulare aller Art. Da PForm von PContainer abgeleitet ist,
* werden alle Formular-Elemente, aber andere Komponenten �ber die Methode add() eingef�gt.
**/
class PForm extends PContainer {
    /**
    * Ziel-Adresse des Formulars ("action")
    **/
    var $m_strDest='';
    /**
    * Methode des Formulars, i.d.R. post od. get
    **/
    var $m_strMethod='';
    /**
    * Typ des Formulars. Wird i.d.R. nur bei File-Uploads ben�tigt.
    **/
    var $m_strEnctype='';

    /**
    * Konstruktor der PForm-Klasse.
    * <b>Parameter:</b>
    * $dest - Zieladresse des Formulars
    * $methode - post oder get
    * $enctype - leer oder "multipart/form-data" f�r File-Upload-Formulare
    * $params - Array mit weiteren Parametern f�rs Form-Tag
    **/
    function PForm($dest=null, $method='post', $enctype='', $params=array()) {
        $this->PContainer();
        if ( is_null($dest) || empty($dest) )	$this->m_strDest=$_SERVER['PHP_SELF'];	else $this->m_strDest=$dest;
        if ( $method!='' )							$this->m_strMethod=$method;				else $this->m_strMethod='post';
        if ( $enctype!='' )							$this->m_strEnctype=$enctype;
        if ( @is_array($params) )					$this->m_strParams=$params;				else $this->m_strParams=array();
    }

    /**
    * �berladene Methode von PComponent. Gibt das Formular samt aller enthaltenen Formularfelder
    * und anderer Komponenten aus.
    * <b>Parameter:</b>
    * keine
    * <b>R�ckgabe:</b>
    * Das Formular als String (HTML)
    **/
    function outputStr() {
        $str="\n<form action=\"".$this->m_strDest."\"";
        if ($this->m_strMethod!="") $str.=" method=\"".$this->m_strMethod."\"";
        if ($this->m_strEnctype!="") $str.=" enctype=\"".$this->m_strEnctype."\"";
        //ge�ndert am 5.11.2001 (R.Kropp): zus�tzliche Param. f�r Form-Tag
        foreach ($this->m_strParams as $key => $value)
            if ($value!="") $str.=" $key=\"$value\""; else $str.=$key;
        $str.=">";

        for($i=0;$i<$this->m_nChildCount;$i++)
               $str.=$this->m_aChildren[$i]->outputStr();

        for($i=$this->m_nChildCount-1;$i>=0;$i--)
               if ($this->isPTag($this->m_aChildren[$i])) $str.=$this->m_aChildren[$i]->outputEndStr();

        $str.="</form>";
        return ($str);
    }
} //class PForm

/**
* PLink ist die Wrapper-Klasse f�r Hyperlinks. Da PLink von PContainer abgeleitet ist,
* k�nnen alle klickbaren Komponenten �ber die Methode add() eingef�gt werden.
**/
class PLink extends PContainer
{
    /**
    * Verweiszieladresse, relativ oder absolut.
    **/
    var $m_strDest;
    /**
    * class-Parameter des <a>-Tags.
    **/
    var $m_strClass;
    /**
    * Name des Zielfensters des Verweises.
    **/
    var $m_strTarget;
    /**
    * Array mit zusatzlichen Parametern.
    **/
    var $m_aLinkParams;

    /**
    * Konstruktor der PLink-Klasse.
    * <b>Parameter:</b>
    * $dest - Zieladresse des Verweises
    * $cont - Inhalt des Verweises (Inhalt zwischen <a ..> und </a>)
    * $params - weitere Parameter als Array im Key=>Value - Format (z.B. array("size"=>"4") )
    * $alt - <alt> und <title> Tags der Eingabefelder
    **/
    function PLink($dest, $cont, $params="", $alt=null)
    {
        $this->PContainer();

        if (is_string($dest)) $this->m_strDest=$dest;
            else $this->m_strDest="";

        if (is_array($params)) $this->m_aLinkParams=$params;
            else $this->m_aLinkParams=array();

        if ( !is_null($alt) )
        {
            $this->m_aLinkParams['alt']=(string)$alt;
            $this->m_aLinkParams['title']=(string)$alt;
        }

        //if ( is_array($m_aParams) ) array_shift($m_aParams, "href"=>$this->m_strDest);
        if (trim($this->m_strDest)!="")
        {
            $tag=new PTag("a",array("href"=>$this->m_strDest));
            $tag->addParam( $this->m_aLinkParams );
            //foreach ($this->m_aLinkParams as $key=>$value) $tag->addParam(");
            $this->add($tag);
        }

        if ($this->isPComponent($cont)) $this->add($cont);
    }



    /**
    * Diese Methode setzt die target- und class-Parameter des zugeh�rigen A-Tags
    * <b>Parameter:</b>
    * $target - target-Parameter des <a>-Tags
    * $class - class-Parameter des <a>-Tags
    **/
    function setTargetClass($target, $class="")
    {
        if (is_string($target)) $this->m_strTarget=$target;
        else $this->m_strTarget="";
        if (is_string($class)) $this->m_strClass=$class;
        else $this->m_strClass="";
        $tag=new PTag("a",array("href"=>$this->m_strDest));
        if ($this->m_strTarget!="") $tag->addParam(array("target"=> $this->m_strTarget));
        if ($this->m_strClass!="") $tag->addParam(array("class"=> $this->m_strClass));
        $this->m_aChildren[0]=$tag;
    }


} //class PLink

/**
* PInput ist die Wrapper-Klasse f�r alle <input>-Tags. Da PInput von PComponent abgeleitet ist,
* kann es in jede beliebige von PContainer abgeleitete Klasse eingef�gt werden.
**/
class PInput extends PComponent
{
    /**
    * Das <input>-Tag vom Typ PTag.
    **/
    var $m_oTag;

    /**
    * Konstruktor der PInput-Klasse.
    * <b>Parameter:</b>
    * $type - Typ des Eingabefeldes (z.B. "text", "password", "hidden", "checkbox", ...)
    * $name - Name des Eingabefeldes
    * $value - Wert des Eingabefeldes (Inhalt)
    * $params - weitere Parameter als Array im Key=>Value - Format (z.B. array("size"=>"4") )
    * $alt - <alt> und <title> Tags der Eingabefelder
    **/
    function PInput($type, $name, $value="", $params=null, $alt=null)
    {
        $this->PComponent();

        $arr=array("type"=>$type, "name"=>$name, "value"=>$value);
        if (is_array($params)) $arr=array_merge($arr,$params);

        if ( !is_null($alt) ) $arr=array_merge( $arr, array("alt"=>(string)$alt, "title"=>(string)$alt) );
        $this->m_oTag=new PTag("input",$arr);
    }

    /**
    * �berladene Methode von PComponent. Gibt das Eigabefeld aus.
    * <b>Parameter:</b>
    * keine
    * <b>R�ckgabe:</b>
    * Das Eingabefeld als String (HTML)
    **/
    function outputStr()
    {
        return $this->m_oTag->outputStr();
    }
} //class PInput

/**
* Die Klasse PSelect ist die Wrapper-Klasse f�r <select>-Tags (Auswahlfelder). Sie verwaltet alle
* Optionen und die Vorauswahl von Optionen. Es werden implizit auch Auswahlfelder mit mehr als
* einer Auswahl unterst�tzt.
**/
class PSelect extends PComponent {

    /**
    * assoziatives Array der Eintr�ge
    **/
    var $m_aEntries;
    /**
    * Die ausgew�hlte Option bzw. auch Optionen (Array).
    **/
    var $m_oSelVal;

    var $m_behavior;
    var $m_oTag;

    /**
    * Konstruktor der PSelect-Klasse
    * <b>Parameter</b>
    * $name - Name des Auswahlfeldes
    * $params - weitere Parameter des <select>-Tags. Z.B. multiple f�r Mehrfachauswahlen
    * $selval - Vorauswahl des Auswahlfeldes, auch Arrays f�r Mehrfachauswahlen
    * Konstante -	PSELECT_DUPLICATE_VALUES	- Standard, Werte d�rfen doppelt vorkommen, angezeigter Text nicht (bisheriges Verhalten)
    *					PSELECT_DUPLICATE_NAMES	- Beschreibungstexte d�rfen doppelt vorkommen, Werte jedoch nicht
    *					PSELECT_DUPLICATE_BOTH		- Werte und Bezeichner d�rfen beliebig oft auftreten
    *					PSELECT_NO_DUPLICATES		- weder Werte noch angezeigter Text d�rfen mehrfach auftreten (zu letzt hinzugef�gte Option �berschreibt vorhergehende)
    **/
    function PSelect($name, $params = null, $selval = null, $behavior = PSELECT_DUPLICATE_VALUES) {
        $this->PComponent();

        $arr=array('name'=>$name);
        if ( is_array($params) ) $arr=array_merge($arr, $params);
        $this->m_oTag     = new PTag('select', $arr);
        $this->m_aEntries = array();
        if ( is_array($selval) ) $this->m_oSelVal = $selval; else $this->m_oSelVal = array($selval);
        $this->m_behavior = $behavior;
    }
    // PSelect


    /**
    * Diese Methode dient zum �berschreiben des Namens bzw. des vorgew�hlten Eintrags des Auswahlfeldes
    * <b>Parameter</b>
    * $name - Name des Auswahlfeldes
    * $selval - Vorauswahl des Auswahlfeldes, auch Arrays f�r Mehrfachauswahlen
    **/
    function reset($name='', $selval = NULL) {
        if ( !empty($name) && is_string($name) ) $this->m_oTag->setParam('name', $name);                 // reset name of this select field
        if ( !is_null($selval) ) {
            if ( is_array($selval) ) $this->m_oSelVal = $selval; else $this->m_oSelVal = array($selval); // reset default value
        }
    }
    // reset

    /**
    * Diese Methode f�gt dem Auswahlfeld einen Option-Eintrag hinzu.
    * <b>Parameter</b>
    * $name - Titel des Option-Eintrags
    * $value - Wert des Option-Eintrags
    **/
    function addEntry($name, $value = NULL, $params = NULL) {
        
        if ( is_null($value) ) $value = $name;
        
        if ( !is_array($name) && !is_object($name) && (string)$name!='' ) {
            switch ($this->m_behavior):
            case PSELECT_DUPLICATE_BOTH:
                $this->m_aEntries[]=array($name, trim($value), $params);
                break;
                
            case PSELECT_DUPLICATE_NAMES:
                $this->m_aEntries[trim($value)]=array($name, trim($value), $params);
                break;

            case PSELECT_NO_DUPLICATES:
                $this->m_aEntries[(trim($name).trim($value))]=array($name, trim($value), $params);
                break;

            default:
                $this->m_aEntries[$name] = array($name, trim($value), $params);
                break;
            endswitch;
        }
    }

    /**
    * �berladene Methode von PComponent. Gibt das Auswahlfeld samt aller Option-Eintr�ge aus.
    * <b>Parameter:</b>
    * keine
    * <b>R�ckgabe:</b>
    * Das Auswahlfeld als String (HTML)
    **/
    function outputStr() {
        // for compatibility with older versions:
        if ( !is_array($this->m_oSelVal) ) $this->m_oSelVal = array($this->m_oSelVal);

        $cont=new PContainer();
        $cont->add($this->m_oTag);
        foreach ($this->m_aEntries as $entry) {
            if ( empty($entry[2]) ) $params = array();
                elseif ( is_array($entry[2]) ) $params = $entry[2];
                    else $params = array($entry[2] => $entry[2]);
            /*
            $params['value'] = $entry[1];
            if ( PUtil::in_array($entry[1], $this->m_oSelVal) ) $params['selected'] = 'selected';
            $opt_container = new PContainer();
            $opt_container->add( new PTag('option', $params) );
            $opt_container->add($entry[0]);
            $cont->add($opt_container);
            */
            //if ( @in_array($entry[1], $this->m_oSelVal) ) $selected='selected'; else $selected='';
            //$cont->add( "\n\t".'<option value="'.$entry[1].'" '.$selected.'>'.$entry[0].'</option>' );

            $selected='';
            if ( PUtil::in_array($entry[1], $this->m_oSelVal) && !empty($this->m_oSelVal) ) $selected = ' selected';
            
            $optiontext="\n\t".'<option value="'.$entry[1].'"';
            foreach($params as $key=>$value) {
              $optiontext.=" $key";
              if (!empty($value))
                $optiontext.="=\"$value\"";            
            }
            $optiontext.=$selected.'>'.$entry[0].'</option>' ;
            
            $cont->add( $optiontext );

        }
        return ( $cont->outputStr() );
    }
} //class PSelect


/**
* Die Klasse PTextArea ist die Wrapper-Klasse f�r Text-Area (mehrzeilige) Textfelder.
**/
class PTextArea extends PComponent
{
    var $m_oTag;

    /**
    * Konstruktor der PTextArea-Klasse
    * <b>Parameter:</b>
    * $cols - Breite des Textfeldes in Zeichen
    * $rows - Zeilen des Textfeldes in Zeichen
    * $name - Name des Textfeldes
    * $text - Inhalt des Textfeldes
    **/
    function PTextArea($cols, $rows, $name, $text="")
    {
        $this->PComponent();

        $this->m_oTag=new PTag("textarea");
        if ($cols>0) $this->m_oTag->setParam("cols", $cols);
        if ($rows>0) $this->m_oTag->setParam("rows", $rows);
        $this->m_oTag->setParam("name", $name);
        $this->m_oTag->setParam("wrap", "virtual");
        if (is_string($text)) $this->m_strText=$text;
    }

    /**
    * �berladene Methode von PComponent. Gibt das Textfeld samt Inhalt aus.
    * <b>Parameter:</b>
    * keine
    * <b>R�ckgabe:</b>
    * Das Textfeld als String (HTML)
    **/
    function outputStr()
    {
        $str=$this->m_oTag->outputStr();
        $str.=$this->m_strText.$this->m_oTag->outputEndStr();
        return ($str);
    }
} //class PTextArea

/**
* PTableLayout kapselt das Design von PTable-Objekten. Damit ist es m�glich ein vorhandenes
* Design in mehreren Tabellen zu benutzen.
**/
class PTableLayout extends PObject
{
    /**
    * Das <table>-Tag der Tabelle als PTag
    **/
    var $m_oHeader;
    /**
    * Das <tr>-Tag der Titelzeile als PTag
    **/
    var $m_oTitle;
    /**
    * Die Zellen der Titelzeile als PTag
    **/
    var $m_oTitleCell;
    /**
    * Das Array der Zeilen (<tr>-Tags) als PTag. Wenn weniger Zeilen vorhanden sind,
    * als sp�ter in der Tabelle so wird periodisch erg�nzt (wieder bei 0 angefangen).
    **/
    var $m_aRows;
    /**
    * Das Array der Spalten (<td>-Tags) als PTag. Wenn weniger Spalten vorhanden sind,
    * als sp�ter in der Tabelle so wird periodisch erg�nzt (wieder bei 0 angefangen).
    **/
    var $m_aCells;
    /**
    * Das <tr>-Tag der Fu�zeile als PTag
    **/
    var $m_oFooter;
    /**
    * Die Zellen der Fu�zeile als PTag
    **/
    var $m_oFooterCell;
    /**
    * Standard-Inhalte der Zellen der Titelzellen
    **/
    var $m_oTitleCellContent;
    /**
    * Standard-Inhalte der Zellen au�erhalb der Titelzellen und Fu�zellen
    **/
    var $m_oCellContent;
    /**
    * Standard-Inhalte der Zellen der Fu�zellen
    **/
    var $m_oFooterCellContent;
    /**
    * Spezielle Parameter der <td>-Tags au�erhalb der Titelzeile und Fu�zeile. Index im
    * Array gibt die Spalte an.
    **/
    var $m_aSpecial;
    /**
    * Spezieller Inhalt der <td>-Tags au�erhalb der Titelzeile und Fu�zeile. Index im
    * Array gibt die Spalte an.
    **/
    var $m_aSpecialContent;
    /**
    * Spezielle Parameter der <td>-Tags in der Titelzeile. Index im
    * Array gibt die Spalte an.
    **/
    var $m_aTitleSpecial;
    /**
    * Spezieller Inhalt <td>-Tags in der Titelzeile. Index im
    * Array gibt die Spalte an.
    **/
    var $m_aTitleSpecialContent;
    /**
    * Spezielle Parameter der <td>-Tags in der Fu�zeile. Index im
    * Array gibt die Spalte an.
    **/
    var $m_aFooterSpecial;
    /**
    * Spezieller Inhalt <td>-Tags in der Fu�zeile. Index im
    * Array gibt die Spalte an.
    **/
    var $m_aFooterSpecialContent;
    /**
    * Array mit Parmetern des <font>-Tags in der Titelzeilezeile, wenn Standardschrift in der Titelzeile gesetzt ist.
    **/
    var $m_oStandardFont;
    /**
    * Array mit Parmetern des <font>-Tags in der Tabellenzellen, wenn Standardschrift gesetzt ist.
    **/
    var $m_oStandardTitleFont;


    /**
    * Konstruktor der PTableLayout-Klasse. Erzeugt ein einfaches Tabellen-Design
    * <b>Parameter:</b>
    * keine
    **/
    function PTableLayout()
    {
        $this->m_oHeader=new PTag("TABLE");
        $this->m_oTitle=new PTag("TR");
        $this->m_oTitleCell=new PTag("TD");
        $this->m_aRows=array(new PTag("TR"));
        $this->m_aCells=array(new PTag("TD"));
        $this->m_oCellContent=new PContainer();
        $this->m_oTitleCellContent=new PContainer();
        $this->m_oFooterCellContent=new PContainer();
        $this->m_oFooter=false;
        //$this->m_oFooterCells=false;
        //$this->m_oFooter=new PTag("TR");
        $this->m_oFooterCell=new PTag("TD"); //R.Kropp: ????
        $this->m_aSpecial=array();
        $this->m_aSpecialContent=array();
        $this->m_aTitleSpecial=array();
        $this->m_aTitleSpecialContent=array();
        $this->m_aFooterSpecial=array();
        $this->m_aFooterSpecialContent=array();
        $this->m_oRepeatTitle=0;
        // Standardschriften setzen
        $this->m_oStandardFont=array();
        $this->m_oStandardTitleFont=array();
    }


    /**
    * Mit dieser Methode kann eine Standardschrift f�r die Titelzeile gesetzt werden.
    * Es k�nnen dabei die Parameter des <font>-Tags gesetzt werden.
    * <b>Parameter:</b>
    * $params - Array der Parameter des <font>-Tags ( z.B. array("size"=>"3", "color"=>"#FFFFFF") )
    **/
    function setStandardTitleFont($params)
    {
        if ( is_array($this->m_oStandardTitleFont) ) $this->m_oStandardTitleFont=$params;
    }

    /**
    * Mit dieser Methode kann eine Standardschrift f�r die Tabellenzellen gesetzt werden.
    * Es k�nnen dabei die Parameter des <font>-Tags gesetzt werden.
    * <b>Parameter:</b>
    * $params - Array der Parameter des <font>-Tags ( z.B. array("size"=>"3", "color"=>"#FFFFFF") )
    **/
    function setStandardFont($params)
    {
        if ( is_array($this->m_oStandardFont) ) $this->m_oStandardFont=$params;
    }

    /**
    * Mit dieser Methode k�nnen die Standardeigenschaften f�r bestimmte Spalten �berschrieben werden.
    * Es k�nnen dabei die Parameter des <td>-Tags der jeweiligen Spalte ver�ndert werden.
    * <b>Parameter:</b>
    * $colindex - Index der zu ver�nderten Spalte
    * $name - Name des Parameters der eingef�gt werden soll, z.B. bgcolor
    * $value - Wert des Parameters der eingef�gt werden soll.
    **/
    function setSpecial($colindex,$name,$value)
    {
        if ( isset($this->m_aSpecial[$colindex]) && is_array($this->m_aSpecial[$colindex]) ) $this->m_aSpecial[$colindex]=array_merge($this->m_aSpecial[$colindex],array($name=>$value));
            else $this->m_aSpecial[$colindex]=array($name=>$value);
    }

    /**
    * Mit dieser Methode k�nnen die Standardeigenschaften f�r bestimmte Spalten �berschrieben werden.
    * Hierbei wird in die entsprechende Spalte besonderer Inhalt eingef�gt.
    * <b>Parameter:</b>
    * $colindex - Index der zu ver�nderten Spalte
    * $comp - Komponente die eingef�gt werden soll.
    **/
    function setSpecialContent($colindex, $comp)
    {
        if ($this->isPComponent($comp))
        {
            if ( isset($this->m_aSpecialContent[$colindex]) && is_array($this->m_aSpecialContent[$colindex]) ) $this->m_aSpecialContent[$colindex]=array_merge($this->m_aSpecialContent[$colindex],array($comp));
                else $this->m_aSpecialContent[$colindex]=array($comp);
        }
    }

    /**
    * Mit dieser Methode k�nne die Standardeigenschaften f�r bestimmte Spalten in der Titelzeile
    * �berschrieben werden.
    * Es k�nnen dabei die Parameter des <td>-Tags der jeweiligen Spalte ver�ndert werden.
    * <b>Parameter:</b>
    * $colindex - Index der zu ver�nderten Spalte
    * $name - Name des Parameters der eingef�gt werden soll, z.B. bgcolor
    * $value - Wert des Parameters der eingef�gt werden soll.
    **/
    function setTitleSpecial($colindex,$name,$value)
    {
        if ( isset($this->m_aTitleSpecial[$colindex]) && is_array($this->m_aTitleSpecial[$colindex]) ) $this->m_aTitleSpecial[$colindex]=array_merge($this->m_aTitleSpecial[$colindex],array($name=>$value));
            else $this->m_aTitleSpecial[$colindex]=array($name=>$value);
    }

    /**
    * Mit dieser Methode k�nne die Standardeigenschaften f�r bestimmte Spalten der Titelzeile
    * �berschrieben werden.
    * Hierbei wird in die entsprechende Spalte besonderer Inhalt eingef�gt.
    * <b>Parameter:</b>
    * $colindex - Index der zu ver�nderten Spalte
    * $comp - Komponente die eingef�gt werden soll.
    **/
    function setTitleSpecialContent($colindex, $comp)
    {
        if ($this->isPComponent($comp))
        {
            if ( isset($this->m_aTitleSpecialContent[$colindex]) && is_array($this->m_aTitleSpecialContent[$colindex]) ) $this->m_aTitleSpecialContent[$colindex]=array_merge($this->m_aTitleSpecialContent[$colindex],array($comp));
                else $this->m_aTitleSpecialContent[$colindex]=array($comp);
        }
    }

    /**
    * Mit dieser Methode k�nne die Standardeigenschaften f�r bestimmte Spalten in der Fu�zeile
    * �berschrieben werden.
    * Es k�nnen dabei die Parameter des <td>-Tags der jeweiligen Spalte ver�ndert werden.
    * <b>Parameter:</b>
    * $colindex - Index der zu ver�nderten Spalte
    * $name - Name des Parameters der eingef�gt werden soll, z.B. bgcolor
    * $value - Wert des Parameters der eingef�gt werden soll.
    **/
    function setFooterSpecial($colindex,$name,$value)
    {
        if ( isset($this->m_aFooterSpecial[$colindex]) && is_array($this->m_aFooterSpecial[$colindex]) ) $this->m_aFooterSpecial[$colindex]=array_merge($this->m_aFooterSpecial[$colindex],array($name=>$value));
            else $this->m_aSpecialFooter[$colindex]=array($name=>$value);
    }

    /**
    * Mit dieser Methode k�nne die Standardeigenschaften f�r bestimmte Spalten der Fu�zeile
    * �berschrieben werden.
    * Hierbei wird in die entsprechende Spalte besonderer Inhalt eingef�gt.
    * <b>Parameter:</b>
    * $colindex - Index der zu ver�nderten Spalte
    * $comp - Komponente die eingef�gt werden soll.
    **/
    function setFooterSpecialContent($colindex, $comp)
    {
        if ($this->isPComponent($comp))
        {
            if ( isset($this->m_aFooterSpecialContent[$colindex]) && is_array($this->m_aFooterSpecialContent[$colindex]) ) $this->m_aFooterSpecialContent[$colindex]=array_merge($this->m_aFooterSpecialContent[$colindex],array($comp));
                else $this->m_aFooterSpecialContent[$colindex]=array($comp);
        }
    }
} //class PTableLayout

/**
* Die Klasse PTable ist die Wrapper-Klasse f�r HTML-Tabellen. Die Klasse ist durch die Verwendung
* von separaten PTableLayout-Objekten designunabh�ngig. Des weiteren wird der gesamte Inhalt der Tabelle
* von dieser Klasse verwaltet.
**/
class PTable extends PComponent
{
    var $m_nCols, $m_nRows, $m_aRows;
    /**
    * Layout-Objekt (PTableLayout) der PTable-Klasse
    **/
    var $m_oLayout;

    /**
    * Konstruktor der PTable-Klasse. Erzeugt eine leere Tabelle mit dem angegebenen Layout.
    * Sollte kein PTableLayout angegeben werden, so wird ein einfaches Standard-Layout
    * (ohne Rahmen) verwendet.
    * <b>Parameter:</b>
    * $layout - PTableLayout-Objekt, das Design der Tabelle
    **/
    function PTable($layout = false) {
        $this->PComponent();
        $this->m_nCols=0;
        $this->m_nRows=0;
        if ($this->isPTableLayout($layout)) $this->m_oLayout=$layout;
            else $this->m_oLayout=new PTableLayout();
    }

    /**
    * addRow f�gt eine Zeile der Tabelle hinzu. Sollten weniger Zellen in der Zeile sein,
    * als in der bisher "gr��ten" Zeile, dann wird die letzte Zeile automatisch vergr��ert.
    * <b>Parameter:</b>
    * $row - Array der jeweiligen Zellen der Zeile, sollten von PComponent abgeleitet sein.
    **/
    function addRow($row) {
        if ( $this->isPComponent($row) ) $row = array($row);
        if ( is_array($row) ) {
           //if ( $this->m_nCols<count($row) ) $this->m_nCols=count($row);
            $this->m_nCols=max(count($row), $this->m_nCols);
           for ($i=0;$i<$this->m_nCols;$i++) {
            if ( $this->isPComponent($row[$i]) ) $this->m_aRows[$this->m_nRows][$i]=$row[$i];
                else $this->m_aRows[$this->m_nRows][$i]=null;
           }
           $this->m_nRows++;
        }
    }

    /**
    * �berpr�ft, ob es sich bei dem gegebenen Objekt um ein PTableLayout handelt.
    * <b>Parameter:</b>
    * $obj - Das zu inspizierende Objekt
    * <b>R�ckgabe:</b>
    * true oder false je nach Ergebnis der �berpr�fung.
    **/
    function isPTableLayout($obj) {
        if ($this->isPObject($obj) && is_array($obj->m_aRows)) return (true);
            else return (false);
    }


    /**
    * �berladene Methode von PComponent. Gibt die Tabelle samt Inhalt aus.
    * <b>Parameter:</b>
    * keine
    * <b>R�ckgabe:</b>
    * Die Tabelle als String (HTML)
    **/
    function outputStr() {
        $rowper=count($this->m_oLayout->m_aRows);
        $colper=count($this->m_oLayout->m_aCells);
        $str=$this->m_oLayout->m_oHeader->outputStr();
        $colcount=0;$colspanned=0;

        // rows, title is row No. 0
        for($i=0;$i<$this->m_nRows;$i++) {
            $str.="\t";
            if ( ($i==0) || ($this->m_oLayout->m_oRepeatTitle>0 && $i%$this->m_oLayout->m_oRepeatTitle==0) ) $str.=$this->m_oLayout->m_oTitle->outputStr();
                else $str.=$this->m_oLayout->m_aRows[$i % $rowper]->outputStr();
            $rowcont=new PContainer();
            $colcount=0;
            $colspanned=0;
            for ($j=0;$j<$this->m_nCols;$j++) {
                $cont=new PContainer();
                if ( ($i==0) || ($this->m_oLayout->m_oRepeatTitle>0 && $i%$this->m_oLayout->m_oRepeatTitle==0) ) {
                    if ($this->isPComponent($this->m_oLayout->m_aTitleSpecialContent[$j])) $col = clone $this->m_oLayout->m_aTitleSpecialContent[$j];
                        else $col = clone $this->m_oLayout->m_oTitleCellContent;
                    $tag = clone $this->m_oLayout->m_oTitleCell;
                    if ( isset($this->m_oLayout->m_aTitleSpecial[$j]) && is_array($this->m_oLayout->m_aTitleSpecial[$j]) )
                        $tag->addParam($this->m_oLayout->m_aTitleSpecial[$j]);
                    $cont->add($tag);
                    // Standardschrift f�r Titelzeile setzen
                    if ( count($this->m_oLayout->m_oStandardTitleFont)>0 ) $cont->add( new PTag('font', $this->m_oLayout->m_oStandardTitleFont) );
                } else {
                    if ( $this->isPComponent($this->m_oLayout->m_aSpecialContent[$j]) ) $col = clone $this->m_oLayout->m_aSpecialContent[$j];
                        else $col = clone $this->m_oLayout->m_oCellContent;
                    $tag = clone $this->m_oLayout->m_aCells[$j % $colper];
                    if ( isset($this->m_oLayout->m_aSpecial[$j]) && is_array($this->m_oLayout->m_aSpecial[$j]) )
                        $tag->addParam($this->m_oLayout->m_aSpecial[$j]);
                    $cont->add($tag);
                    // Standardschrift setzen
                    if ( count($this->m_oLayout->m_oStandardFont)>0 ) $cont->add( new PTag('font', $this->m_oLayout->m_oStandardFont) );
                }

                $text = $this->m_aRows[$i][$j];
                if ( is_string($text) || $this->isPComponent($text) ) $col->add($text);

                if ( !isset($this->m_aRows[$i][$j]) && $colcount>0 ) {
                    $colspanned++;
                    $rowcont->m_aChildren[$colcount-1]->m_aChildren[0]->setParam('colspan', $colspanned+1);
                } else {
                    $cont->add($col);
                    $rowcont->add($cont);
                    $colcount++;
                    $colspanned=0;
                }
            }
            $str.=$rowcont->outputStr();
            if ( ($i==0) || ($this->m_oLayout->m_oRepeatTitle>0 && $i%$this->m_oLayout->m_oRepeatTitle==0) ) $str.=$this->m_oLayout->m_oTitle->outputEndStr();
                else $str.=$this->m_oLayout->m_aRows[$i % $rowper]->outputEndStr();
        }

        // Footer if applicable
        if ( $this->isPComponent($this->m_oLayout->m_oFooter) ) {
            $str.=$this->m_oLayout->m_oFooter->outputStr();									// <tr>-tag
            for ($j=0;$j<$this->m_nCols;$j++) {
                $tag=$this->m_oLayout->m_oFooterCell;											// <td>-tag
                if ( @is_array($this->m_oLayout->m_aFooterSpecial[$j]) ) $tag->addParam($this->m_oLayout->m_aFooterSpecial[$j]);
                $cont=new PContainer();																// container for footer cell content
                $cont->add($tag);

                $special=0;
                if ( @is_array($this->m_oLayout->m_aFooterSpecialContent[$j]) ) {		// special cell content
                    foreach ( $this->m_oLayout->m_aFooterSpecialContent[$j] as $m_aFooterSpecialContent ) {
                        if ( $this->isPComponent($m_aFooterSpecialContent) ) {
                            $special=1;
                            $cont->add( $m_aFooterSpecialContent );
                        }
                    }
                }
                if ( !$special ) $cont->add( $this->m_oLayout->m_oFooterCellContent );	// if no special content, add standard content
                $str.=$cont->outputStr();
            }
            $str.=$this->m_oLayout->m_oFooter->outputEndStr();								// </td>-tag
        }
        $str.=$this->m_oLayout->m_oHeader->outputEndStr();									// last line (</tr>-tag)
        return ($str);
    } //outputStr

} //class PTable

/**
* PImage ist die Wrapper-Klasse des <img>-Tags.
**/
class PImage extends PTag
{

    /**
    * Konstruktor der PImage-Klasse
    * <b>Parameter:</b>
    * $src - Quelle des Bildes (URL)
    * $params - Weitere Parameter des <img>-Tags als Array (key=>value), z.B. width, height, ...
    * $alt - <alt> und <title> Tags der Eingabefelder
    **/
    function PImage($src, $params=null, $alt=null)
    {
        $this->PTag("IMG", array("src"=>$src));
        $this->addParam($params);

        if ( !is_null($alt) ) $this->addParam( array("alt"=>(string)$alt, "title"=>(string)$alt) );
    }

} //class PImage


?>