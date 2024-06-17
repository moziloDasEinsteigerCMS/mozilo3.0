<?php if(!defined('IS_CMS')) die();

class Contact extends Plugin {

    function getDefaultSettings($only_formcalcs = false) {
        $tmp = array(
            "formularmail" => "",
            "contactformwaittime" => "15",
            "contactformusespamprotection" => "true",
            "contactformcalcs" => "3 + 7 = 10<br />5 - 3 = 2<br />1 plus 1 = 2<br />17 minus 7 = 10<br />4 * 2 = 8<br />3x3 = 9<br />2 geteilt bei 2 = 1<br />Abraham Lincols Vorname = Abraham<br />James Bonds Nachname = Bond<br />bronze, silber, ... ? = gold",
            "titel_name" => "",
            "titel_name_show" => "true",
            "titel_name_mandatory" => "false",
            "titel_subject" => "",
            "titel_subject_show" => "true",
            "titel_subject_mandatory" => "false",
            "titel_website" => "",
            "titel_website_show" => "true",
            "titel_website_mandatory" => "false",
            "titel_mail" => "",
            "titel_mail_show" => "true",
            "titel_mail_mandatory" => "false",
            "titel_mail_send_copy" => "false",
            "titel_message" => "",
            "titel_message_show" => "true",
            "titel_message_mandatory" => "false",
            "titel_privacy" => "",
            "titel_privacy_show" => "true",
            "titel_privacy_mandatory" => "false",
            "contact_details_scheme" => "person"
        );
        if($only_formcalcs)
            return $tmp["contactformcalcs"];
        return $tmp;
    }

    function getContent($value) {
        global $CMS_CONF;
        global $contactformcalcs;
        global $lang_contact;

        $dir = PLUGIN_DIR_REL."Contact/";
        $lang_contact = new Language($dir."sprachen/cms_language_".$CMS_CONF->get("cmslanguage").".txt");

        // existiert eine Mailadresse? Wenn nicht: Das Kontaktformular gar nicht anzeigen!
        if ($this->settings->get("contact_form") == "true") {
        if(strlen($this->settings->get("formularmail")) < 1) {
            return '<span class="deadlink">'.$lang_contact->getLanguageValue("tooltip_no_mail_error")."</span>";
        }
     }

        if(strlen($this->settings->get("contactformcalcs")) < 5)
            $this->settings->set("contactformcalcs",$this->getDefaultSettings(true));
            
               $return = '<div class="contact_form">';
               if ($this->settings->get("contact_details") == "true") {
              	$return .= '<h3 class="heading3">'.$lang_contact->getLanguageValue("contactdetails_heading").'</h3>';
              	$return .= '<div class="contact_details" itemscope="" itemtype="https://schema.org/';
              	if($this->settings->get("contact_details_scheme") == "person") {
              	$return .= 'Person';              
              } elseif($this->settings->get("contact_details_scheme") == "organisation") {
              	$return .= 'Organization';              
              } elseif($this->settings->get("contact_details_scheme") == "localbusiness") {
              	$return .= 'LocalBusiness';              
              }              
              $return .= '">';
              
              if ($this->settings->get("contact_details_name_show") == "true") {
               	$return .= '<div itemprop="name">'.$this->settings->get("contact_details_name").'</div>';
               } 
               
					$return .= '<div itemprop="address" itemscope itemtype="https://schema.org/PostalAddress">';
               if ($this->settings->get("contact_details_street_show") == "true") {
               	$return .= '<div itemprop="streetAddress">'.$this->settings->get("contact_details_street").'</div>';
               }  
               if ($this->settings->get("contact_details_plz_show") == "true") {
               	$return .= '<span itemprop="postalCode">'.$this->settings->get("contact_details_plz").' </span>';
               }
                if ($this->settings->get("contact_details_city_show") == "true") {
                	$return .='<span itemprop="addressLocality">'.$this->settings->get("contact_details_city").'</span>';
                }                  
               $return .= '</div>';
               if ($this->settings->get("contact_details_phone_show") == "true") {
               	$return .= '<div itemprop="telephone">'.$this->settings->get("contact_details_phone").'</div>';
               }
               if ($this->settings->get("contact_details_email_show") == "true") {
               	$return .= '<div itemprop="email">'.$this->settings->get("contact_details_email").'</div>';
               }
               if ($this->settings->get("contact_details_website_show") == "true") {
               	$return .= '<div itemprop="url">'.$this->settings->get("contact_details_website").'</div>';
               }
               
               $return .= '</div>';
               } 
               
                              
               if ($this->settings->get("contact_social") == "true") {
              	$return .= '<h3 class="heading3">'.$lang_contact->getLanguageValue("contactsocial_heading").'</h3>';
              	$return .= '<div class="contact_social">';
              	if ($this->settings->get("contact_social_fb_show") == "true") {
               	$return .= '<div><a href="'.$this->settings->get("contact_social_fb").'" aria-label="Facebook Link" title= "Facebook" target="_blank">Facebook</a></div>';
               }
               if ($this->settings->get("contact_social_mastodon_show") == "true") {
               	$return .= '<div><a href="'.$this->settings->get("contact_social_mastodon").'" aria-label="Mastodon Link" title= "Mastodon" target="_blank">Mastodon</a></div>';
               }
					if ($this->settings->get("contact_social_insta_show") == "true") {
               	$return .= '<div><a href="'.$this->settings->get("contact_social_insta").'" aria-label="Instagram Link" title= "Instagram" target="_blank">Instagram</a></div>';
               }
               if ($this->settings->get("contact_social_pinterest_show") == "true") {
               	$return .= '<div><a href="'.$this->settings->get("contact_social_pinterest").'" aria-label="Pinterest Link" title= "Pinterest" target="_blank">Pinterest</a></div>';
               }
               if ($this->settings->get("contact_social_linkedin_show") == "true") {
               	$return .= '<div><a href="'.$this->settings->get("contact_social_linkedin").'" aria-label="LinkedIn Link" title= "LinkedIn" target="_blank">LinkedIn</a></div>';
               }
					if ($this->settings->get("contact_social_youtube_show") == "true") {
               	$return .= '<div><a href="'.$this->settings->get("contact_social_youtube").'" aria-label="Youtube Link" title= "Youtube" target="_blank">Youtube</a></div>';
               }
              	$return .= '</div>';
              }
               
               if ($this->settings->get("contact_form") == "true") {

        require_once($dir."func_contact.php");
        
     	  $return .= '<h3 class="heading3">'.$lang_contact->getLanguageValue("contactform_heading").'</h3>';
        $return .= buildContactForm($this->settings);
        
        /* security hotfix 2017-06-14 */
        $return = str_replace(array('[',']','{','}','|'), array('&#091;','&#093;','&#123;','&#125;','&#124;'), $return);    
        
        
     }    
        
         $return .= '</div>';
        
        return $return;

    } // function getContent

    /***************************************************************
    * 
    * Gibt die Konfigurationsoptionen als Array zurück.
    * Ist keine Konfiguration nötig, ist dieses Array leer.
    * 
    ***************************************************************/
    function getConfig() {
        global $lang_contact_admin;

        $config = array();
        $config['formularmail']  = array(
            "type" => "text",
            "description" => $lang_contact_admin->get("config_text_formularmail"),
            "maxlength" => "100",
            "regex" => MAIL_REGEX,
            "regex_error" => $lang_contact_admin->get("config_error_formularmail")
        );
        $config['contactformwaittime']  = array(
            "type" => "text",
            "description" => $lang_contact_admin->get("config_text_contactformwaittime"),
            "maxlength" => "4",
            "size" => "3",
            "regex" => "/^[\d+]+$/",
            "regex_error" => getLanguageValue("check_digit")
        );
        $config['contactformusespamprotection'] = array(
            "type" => "checkbox",
            "description" => $lang_contact_admin->get("config_text_contactformusespamprotection")
        );
        $config['contactformcalcs'] = array(
            "type" => "textarea",
            "rows" => "10",
            "description" => $lang_contact_admin->get("config_titel_spam_question"),
        );
        # name
        $config['titel_name']  = array(
            "type" => "text",
            "description" => $lang_contact_admin->get("config_input_contact_name"),
            "maxlength" => "100",
        );
        $config['titel_name_show'] = array(
            "type" => "checkbox",
            "description" => $lang_contact_admin->get("config_titel_contact_show")
        );
        $config['titel_name_mandatory'] = array(
            "type" => "checkbox",
            "description" => $lang_contact_admin->get("config_titel_contact_mandatory")
        );
        # subject
        $config['titel_subject']  = array(
            "type" => "text",
            "description" => $lang_contact_admin->get("config_input_contact_subject"),
            "maxlength" => "100",
        );
        $config['titel_subject_show'] = array(
            "type" => "checkbox",
            "description" => $lang_contact_admin->get("config_titel_contact_show")
        );
        $config['titel_subject_mandatory'] = array(
            "type" => "checkbox",
            "description" => $lang_contact_admin->get("config_titel_contact_mandatory")
        );
        # website
        $config['titel_website']  = array(
            "type" => "text",
            "description" => $lang_contact_admin->get("config_input_contact_website"),
            "maxlength" => "100",
        );
        $config['titel_website_show'] = array(
            "type" => "checkbox",
            "description" => $lang_contact_admin->get("config_titel_contact_show")
        );
        $config['titel_website_mandatory'] = array(
            "type" => "checkbox",
            "description" => $lang_contact_admin->get("config_titel_contact_mandatory")
        );
        # mail
        $config['titel_mail']  = array(
            "type" => "text",
            "description" => $lang_contact_admin->get("config_input_contact_mail"),
            "maxlength" => "100",
        );
        $config['titel_mail_send_copy']  = array(
            "type" => "checkbox",
            "description" => $lang_contact_admin->get("config_input_contact_mail_send_copy"),
        );
        $config['titel_mail_show'] = array(
            "type" => "checkbox",
            "description" => $lang_contact_admin->get("config_titel_contact_show")
        );
        $config['titel_mail_mandatory'] = array(
            "type" => "checkbox",
            "description" => $lang_contact_admin->get("config_titel_contact_mandatory")
        );
        # message
        $config['titel_message']  = array(
            "type" => "text",
            "description" => $lang_contact_admin->get("config_input_contact_textarea"),
            "maxlength" => "100",
        );
        $config['titel_message_show'] = array(
            "type" => "checkbox",
            "description" => $lang_contact_admin->get("config_titel_contact_show")
        );
        $config['titel_message_mandatory'] = array(
            "type" => "checkbox",
            "description" => $lang_contact_admin->get("config_titel_contact_mandatory")
        );

        $config['titel_privacy']  = array(
            "type" => "text",
            "description" => $lang_contact_admin->get("config_input_contact_privacy"),
            "maxlength" => "100",
        );
        $config['titel_privacy_show'] = array(
            "type" => "checkbox",
            "description" => $lang_contact_admin->get("config_titel_contact_show")
        );
        $config['titel_privacy_mandatory'] = array(
            "type" => "checkbox",
            "description" => $lang_contact_admin->get("config_titel_contact_mandatory")
        );
        $config['category']  = array(
            "type" => "text",                           
            "description" => $lang_contact_admin->get("config_category"), 
            "maxlength" => "100"
        );
        $config['data_protection_page']  = array(
            "type" => "text",
            "description" => $lang_contact_admin->get("config_data_protection_page"), 
            "maxlength" => "100"
        );
        
        $config['contact_details'] = array(
            "type" => "checkbox",
            "description" => $lang_contact_admin->get("config_contact_details"), 
        );
        
        $config['contact_details_scheme'] = array(
            "type" => "select",
            "description" => $lang_contact_admin->get("config_contact_details_scheme"), 
            "descriptions" => array(
        "person" => "Person",
        "organisation" => "Organisation",
        "localbusiness" => "Unternehmen"
      ),
      "multiple" => false
        );
        
        $config['contact_details_name']  = array(
            "type" => "text",
            "description" => $lang_contact_admin->get("config_contact_details_name"),
            "maxlength" => "100",
        );
        $config['contact_details_name_show'] = array(
            "type" => "checkbox",
            "description" => $lang_contact_admin->get("config_contact_details_name_show")
        );
        
                $config['contact_details_street']  = array(
            "type" => "text",
            "description" => $lang_contact_admin->get("config_contact_details_street"),
            "maxlength" => "100",
        );
        $config['contact_details_street_show'] = array(
            "type" => "checkbox",
            "description" => $lang_contact_admin->get("config_contact_details_street_show")
        );
        
        $config['contact_details_plz']  = array(
            "type" => "text",
            "description" => $lang_contact_admin->get("config_contact_details_plz"),
            "maxlength" => "100",
        );
        $config['contact_details_plz_show'] = array(
            "type" => "checkbox",
            "description" => $lang_contact_admin->get("config_contact_details_plz_show")
        );
        
       $config['contact_details_city']  = array(
            "type" => "text",
            "description" => $lang_contact_admin->get("config_contact_details_city"),
            "maxlength" => "100",
        );
        $config['contact_details_city_show'] = array(
            "type" => "checkbox",
            "description" => $lang_contact_admin->get("config_contact_details_city_show")
        );
        
        $config['contact_details_phone']  = array(
            "type" => "text",
            "description" => $lang_contact_admin->get("config_contact_details_phone"),
            "maxlength" => "100",
        );
        $config['contact_details_phone_show'] = array(
            "type" => "checkbox",
            "description" => $lang_contact_admin->get("config_contact_details_phone_show")
        );
        
        $config['contact_details_email']  = array(
            "type" => "text",
            "description" => $lang_contact_admin->get("config_contact_details_email"),
            "maxlength" => "100",
        );
        $config['contact_details_email_show'] = array(
            "type" => "checkbox",
            "description" => $lang_contact_admin->get("config_contact_details_email_show")
        );
        
        $config['contact_details_website']  = array(
            "type" => "text",
            "description" => $lang_contact_admin->get("config_contact_details_website"),
            "maxlength" => "100",
        );
        $config['contact_details_website_show'] = array(
            "type" => "checkbox",
            "description" => $lang_contact_admin->get("config_contact_details_website_show")
        );
        
        $config['contact_social'] = array(
            "type" => "checkbox",
            "description" => $lang_contact_admin->get("config_contact_social"), 
        );
        
        $config['contact_social_fb']  = array(
            "type" => "text",
            "description" => $lang_contact_admin->get("config_contact_social_fb"),
            "maxlength" => "100",
        );
        $config['contact_social_fb_show'] = array(
            "type" => "checkbox",
            "description" => $lang_contact_admin->get("config_contact_social_fb_show")
        );
        
        $config['contact_social_mastodon']  = array(
            "type" => "text",
            "description" => $lang_contact_admin->get("config_contact_social_mastodon"),
            "maxlength" => "100",
        );
        $config['contact_social_mastodon_show'] = array(
            "type" => "checkbox",
            "description" => $lang_contact_admin->get("config_contact_social_mastodon_show")
        );
        
        $config['contact_social_insta']  = array(
            "type" => "text",
            "description" => $lang_contact_admin->get("config_contact_social_insta"),
            "maxlength" => "100",
        );
        $config['contact_social_insta_show'] = array(
            "type" => "checkbox",
            "description" => $lang_contact_admin->get("config_contact_social_insta_show")
        );
        
        $config['contact_social_pinterest']  = array(
            "type" => "text",
            "description" => $lang_contact_admin->get("config_contact_social_pinterest"),
            "maxlength" => "100",
        );
        
        $config['contact_social_pinterest_show'] = array(
            "type" => "checkbox",
            "description" => $lang_contact_admin->get("config_contact_social_pinterest_show")
        );
        
        $config['contact_social_linkedin']  = array(
            "type" => "text",
            "description" => $lang_contact_admin->get("config_contact_social_linkedin"),
            "maxlength" => "100",
        );
        
        $config['contact_social_linkedin_show'] = array(
            "type" => "checkbox",
            "description" => $lang_contact_admin->get("config_contact_social_linkedin_show")
        );
        
         $config['contact_social_youtube']  = array(
            "type" => "text",
            "description" => $lang_contact_admin->get("config_contact_social_youtube"),
            "maxlength" => "100",
        );
        
        $config['contact_social_youtube_show'] = array(
            "type" => "checkbox",
            "description" => $lang_contact_admin->get("config_contact_social_youtube_show")
        );
        
        $config['contact_form'] = array(
            "type" => "checkbox",
            "description" => $lang_contact_admin->get("config_contact_form"),
        );
        
        $config['--template~~'] = ''
        .'<div class="card mb">'
        .'<div class="flex mb">'
                    .'<div class="mo-in-li-l">{contact_details_description}</div>'
                    .'<div class="mo-in-li-r">{contact_details_checkbox}</div>'
						.'</div>'
						.'<div class="flex mb">'
						.'<div class="mo-in-li-l">{contact_details_scheme_description}</div>'
                    .'<div class="mo-in-li-r">{contact_details_scheme_select}</div>'
						.'</div>'
						.'<div class="mb">'
                    .'<p>'.$lang_contact_admin->get("config_text_contact_details").'</p>'
                    .'<div class="flex mb thead">'
                        .'<div class="mo-bold">'.$lang_contact_admin->get("config_titel_contact_help").'</div>'
                        .'<div class="mo-bold" style="flex:2">'.$lang_contact_admin->get("config_titel_contact_input").'</div>'
                        .'<div class="mo-bold mo-align-center">'.$lang_contact_admin->get("config_titel_contact_show").'</div>'
                    .'</div>'
                    .'<div class="flex mb">'
                    		.'<div>{contact_details_name_description}</div>'
                        .'<div style="flex:2">{contact_details_name_text}</div>'
                        .'<div class="mo-align-center">{contact_details_name_show_checkbox}</div>'
                        .'</div>'
                    .'<div class="flex mb">'
                    		.'<div>{contact_details_street_description}</div>'
                        .'<div style="flex:2">{contact_details_street_text}</div>'
                        .'<div class="mo-align-center">{contact_details_street_show_checkbox}</div>'
                        .'</div>'                        
						.'<div class="flex mb">'
                    		.'<div>{contact_details_plz_description}</div>'
                        .'<div style="flex:2">{contact_details_plz_text}</div>'
                        .'<div class="mo-align-center">{contact_details_plz_show_checkbox}</div>'
                        .'</div>'                        
                   .'<div class="flex mb">'
                    		.'<div>{contact_details_city_description}</div>'
                        .'<div style="flex:2">{contact_details_city_text}</div>'
                        .'<div class="mo-align-center">{contact_details_city_show_checkbox}</div>'
                        .'</div>'                        
                  .'<div class="flex mb">'
                    		.'<div>{contact_details_phone_description}</div>'
                        .'<div style="flex:2">{contact_details_phone_text}</div>'
                        .'<div class="mo-align-center">{contact_details_phone_show_checkbox}</div>'
                        .'</div>'                        
                  .'<div class="flex mb">'
                    		.'<div>{contact_details_email_description}</div>'
                        .'<div style="flex:2">{contact_details_email_text}</div>'
                        .'<div class="mo-align-center">{contact_details_email_show_checkbox}</div>'
                        .'</div>'                        
                  .'<div class="flex">'
                    		.'<div>{contact_details_website_description}</div>'
                        .'<div style="flex:2">{contact_details_website_text}</div>'
                        .'<div class="mo-align-center">{contact_details_website_show_checkbox}</div>'
                        .'</div>'      
                        
                .'</div></div>'              
                                .'<div class="card mb">'
                                .'<div class="flex mb">'
                                .'<div class="mo-in-li-l">{contact_social_description}</div>'
                    .'<div class="mo-in-li-r">{contact_social_checkbox}</div>'
						.'</div>'
						.'<div class="mb">'
                    .'<p>'.$lang_contact_admin->get("config_text_contact_social").'</p>'
                    .'<div class="flex mb">'
                        .'<div class="mo-nowrap mo-bold">'.$lang_contact_admin->get("config_titel_contact_help").'</div>'
                        .'<div class="mo-nowrap mo-bold" style="flex:2">'.$lang_contact_admin->get("config_titel_social_input").'</div>'
                        .'<div class="mo-nowrap mo-bold mo-align-center">'.$lang_contact_admin->get("config_titel_contact_show").'</div>'
                    .'</div>'
                    .'<div class="flex mb">'
                    .'<div class="mo-nowrap mo-padding-top">{contact_social_fb_description}</div>'
                        .'<div class="mo-padding-top" style="flex:2">{contact_social_fb_text}</div>'
                        .'<div class="mo-align-center">{contact_social_fb_show_checkbox}</div>'
                        .'</div>'
                    .'<div class="flex mb">'
                    .'<div class="mo-nowrap mo-padding-top">{contact_social_mastodon_description}</div>'
                        .'<div class="mo-padding-top" style="flex:2">{contact_social_mastodon_text}</div>'
                        .'<div class="mo-align-center">{contact_social_mastodon_show_checkbox}</div>'
                        .'</div>'
                        
						.'<div class="flex mb">'
                    .'<div class="mo-nowrap mo-padding-top">{contact_social_insta_description}</div>'
                        .'<div class="mo-padding-top" style="flex:2">{contact_social_insta_text}</div>'
                        .'<div class="mo-align-center">{contact_social_insta_show_checkbox}</div>'
                        .'</div>'    
                        .'<div class="flex mb">'
                    .'<div class="mo-nowrap mo-padding-top">{contact_social_pinterest_description}</div>'
                        .'<div class="mo-padding-top" style="flex:2">{contact_social_pinterest_text}</div>'
                        .'<div class="mo-align-center">{contact_social_pinterest_show_checkbox}</div>'
                        .'</div>'  
                        
                        .'<div class="flex mb">'
                    .'<div class="mo-nowrap mo-padding-top">{contact_social_linkedin_description}</div>'
                        .'<div class="mo-padding-top" style="flex:2">{contact_social_linkedin_text}</div>'
                        .'<div class="mo-align-center">{contact_social_linkedin_show_checkbox}</div>'
                        .'</div>'    
                        
                        .'<div class="flex mb">'
                    .'<div class="mo-nowrap mo-padding-top">{contact_social_youtube_description}</div>'
                        .'<div class="mo-padding-top" style="flex:2">{contact_social_youtube_text}</div>'
                        .'<div class="mo-align-center">{contact_social_youtube_show_checkbox}</div>'
                        .'</div>'    
                        
                .'</div></div>'                              
                                .'<div class="card">'
                                .'<div class="flex mb">'
                    .'<div class="mo-in-li-l">{contact_form_description}</div>'
                    .'<div class="mo-in-li-r">{contact_form_checkbox}</div>'
                    .'</div>'
                    .'<div class="flex mb column">'
                    .'<div class="mo-in-li-l">{formularmail_description}</div>'
                    .'<div class="mo-in-li-r">{formularmail_text}</div>'
                    .'</div>'
                    .'<div class="flex mb column">'
                    .'<div class="mo-in-li-l">{contactformwaittime_description}</div>'
                    .'<div class="mo-in-li-r">{contactformwaittime_text}</div>'
                    .'</div>'
                    .'<div class="mb" style="border-bottom: 1px solid var(--border-color)"></div>'
                    .'<div class="mb">'
                    .'<p>'.$lang_contact_admin->get("config_text_contact").'</p>'
                .'<div class="flex mb" style="flex-wrap:wrap">'
                        .'<div class="mo-nowrap mo-bold">'.$lang_contact_admin->get("config_titel_contact_help").'</div>'
                        .'<div class="mo-nowrap mo-bold" style="flex:2">'.$lang_contact_admin->get("config_titel_contact_input").'</div>'
                        .'<div class="mo-nowrap mo-bold mo-align-center">'.$lang_contact_admin->get("config_titel_contact_show").'</div>'
                        .'<div class="mo-nowrap mo-bold mo-align-center">'.$lang_contact_admin->get("config_titel_contact_mandatory").'</div>'
                    .'</div>'
                        .'<div class="flex mb">'
                        .'<div class="mo-nowrap mo-padding-top" data-label="desc">{titel_name_description}</div>'
                        .'<div class="mo-padding-top" data-label="text" style="flex:2">{titel_name_text}</div>'
                        .'<div class="mo-align-center mo-padding-top" data-label="show">{titel_name_show_checkbox}</div>'
                        .'<div class="mo-align-center mo-padding-top" data-label="mandatory">{titel_name_mandatory_checkbox}</div>'
					.'</div>'
                        .'<div class="flex mb">'
                        .'<div class="mo-nowrap mo-padding-top" data-label="desc">{titel_subject_description}</div>'
                        .'<div class="mo-padding-top" data-label="text" style="flex:2">{titel_subject_text}</div>'
                        .'<div class="mo-align-center mo-padding-top" data-label="show">{titel_subject_show_checkbox}</div>'
                        .'<div class="mo-align-center mo-padding-top" data-label="mandatory">{titel_subject_mandatory_checkbox}</div>'
                    .'</div>'
                        .'<div class="flex mb">'
                        .'<div class="mo-nowrap mo-padding-top" data-label="desc">{titel_website_description}</div>'
                        .'<div class="mo-padding-top" data-label="text" style="flex:2">{titel_website_text}</div>'
                        .'<div class="mo-align-center mo-padding-top" data-label="show">{titel_website_show_checkbox}</div>'
                        .'<div class="mo-align-center mo-padding-top" data-label="mandatory">{titel_website_mandatory_checkbox}</div>'
                    .'</div>'
                        .'<div class="flex mb">'
                        .'<div class="mo-nowrap mo-padding-top" data-label="desc">{titel_mail_description}'
                        .'<div>{titel_mail_send_copy_description} {titel_mail_send_copy_checkbox}</div>'
                        .'</div>'
                        .'<div class="mo-padding-top" data-label="text" style="flex:2">{titel_mail_text}</div>'
                        .'<div class="mo-align-center mo-padding-top" data-label="show">{titel_mail_show_checkbox}</div>'
                        .'<div class="mo-align-center mo-padding-top" data-label="mandatory">{titel_mail_mandatory_checkbox}</div>'
                    .'</div>'
                        .'<div class="flex mb">'
                        .'<div class="mo-nowrap mo-padding-top">{titel_message_description}</div>'
                        .'<div class="mo-padding-top" data-label="text" style="flex:2">{titel_message_text}</div>'
                        .'<div class="mo-align-center mo-padding-top" data-label="show">{titel_message_show_checkbox}</div>'
                        .'<div class="mo-align-center mo-padding-top" data-label="mandatory">{titel_message_mandatory_checkbox}</div>'
                    .'</div>'
                        .'<div class="flex mb">'
                        .'<div class="mo-nowrap mo-padding-top">{titel_privacy_description}</div>'
                        .'<div class="mo-padding-top" data-label="text" style="flex:2">{titel_privacy_text}</div>'
                        .'<div class="mo-align-center mo-padding-top" data-label="show">{titel_privacy_show_checkbox}</div>'
                        .'<div class="mo-align-center mo-padding-top" data-label="mandatory">{titel_privacy_mandatory_checkbox}</div>'
                    .'</div>'
                    .'</div>'
                    .'<div class="mb" style="border-bottom: 1px solid var(--border-color)"></div>'
                    .'<div class="flex mb">'
                    .'<div class="mo-in-li-l">{contactformusespamprotection_description}</div>'
                    .'<div class="mo-in-li-r">{contactformusespamprotection_checkbox}</div>'
                    .'</div>'
                    .'<div class="flex mb column">'
                    .'<div class="mo-in-li-l">{contactformcalcs_description}</div>'
                    .'<div class="mo-in-li-r">{contactformcalcs_textarea}</div>'
                    .'</div>'
                    .'<div class="mb" style="border-bottom: 1px solid var(--border-color)"></div>'
                    .'<div class="flex mb column">'
                    .'<div class="mo-in-li-l">{category_description}</div>'
                    .'<div class="mo-in-li-r">{category_text}</div>'
                    .'</div>'
                    .'<div class="flex mb column">'
							.'<div class="mo-in-li-l">{data_protection_page_description}</div>'
                    .'<div class="mo-in-li-r">{data_protection_page_text}</div>'
                    .'</div></div>' ;
                    
        return $config;
    } // function getConfig    
    

    /***************************************************************
    * 
    * Gibt die Plugin-Infos als Array zurück - in dieser 
    * Reihenfolge:
    *   - Name und Version des Plugins
    *   - für moziloCMS-Version
    *   - Kurzbeschreibung
    *   - Name des Autors
    *   - Download-URL
    *   - Platzhalter für die Selectbox
    * 
    ***************************************************************/
    function getInfo() {
        global $ADMIN_CONF;
        global $lang_contact_admin;
        $dir = PLUGIN_DIR_REL."Contact/";
        $language = $ADMIN_CONF->get("language");
        $lang_contact_admin = new Properties($dir."sprachen/admin_language_".$language.".txt",false);

        $info = array(
            // Plugin-Name + Version
            "Version: 3.0",
            // moziloCMS-Version
            "2.0 / 3.0",
            // Kurzbeschreibung nur <span> und <br /> sind erlaubt
            $lang_contact_admin->get("config_help_contact"),
            // Name des Autors
            "mozilo",
            // Download-URL
            "",
            // Platzhalter für die Selectbox in der Editieransicht 
            // - ist das Array leer, erscheint das Plugin nicht in der Selectbox
            array(
                '{Contact}' => $lang_contact_admin->get("toolbar_platzhalter_contact")
            )
        );
        return $info;
    } // function getInfo

}

?>
