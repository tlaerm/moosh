<?php
/**
 * moosh - Moodle Shell
 *
 * @copyright  2012 onwards Tomasz Muras
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 ************************
 * Module: LabelCreate  *
 ************************
 * version 1.00
 *
 * by François Parlant november 2022
 * Github: https://github.com/fxpar/
 * Linkedin: https://www.linkedin.com/in/francois-parlant/
 *
 *
 *PARAMETERS
 * section [-s|--section numsection, default 1] indicate the section where the label is added
 * completion [-c|--completion 0 or 1, default 0] indicate if the activity completion is enabled. Default 0 (no)
 * text: -t --text
 * course id, compulsory argument, at the end
 *
 * EXAMPLE 1
 * adds "my text" in section 3 of course 2 with completion
 * moosh -n label-create -s 3 -c 1 -t "my text" 2 
 *
 * EXAMPLE 2
 * adds a a title and a paragraph in section 3 of course 2 without completion
 * notice how double quotes are escaped in the text
 * moosh -n label-create -s 3 -t "<h1 style=\"color:red;\">my red title</h1> <p>my text</p>" 2
 *
 *
 */

namespace Moosh\Command\Moodle39\Dev;
use Moosh\MooshCommand;

class LabelCreate extends MooshCommand
{
    public function __construct()
    {
        parent::__construct('create', 'label');
		
		// added fxp
		$this->addOption('s|section:', 'section number', '1');
		$this->addOption('cp|completion:', 'completion status', '0');
		$this->addOption('t|text:', 'make sure this piece of text is included in the random content', NULL);
		
        $this->addArgument('courseid');

        

    }


    public function execute()
    {
        //some variables you may want to use
        //$this->cwd - the directory where moosh command was executed
        //$this->mooshDir - moosh installation directory
        //$this->expandedOptions - commandline provided options, merged with defaults
        //$this->topDir - top Moodle directory

        global $CFG, $DB, $USER;

        require_once($CFG->dirroot . '/course/lib.php');
        require_once($CFG->dirroot . '/course/modlib.php');

        $length = 64;

        if ($this->expandedOptions['text']) {
            $split = rand(0, $length);
            
            $text =  $this->expandedOptions['text'];
        } else {
            $text = generate_html_page($length);
        }

        $moduleinfo = new \stdClass();
        $moduleinfo->introeditor =
            array(
                'text' => $text,
                'format' => '1',
                'itemid' => NULL,
            );
        $moduleinfo->visible = '1';
        $moduleinfo->visibleoncoursepage = 1;
        $moduleinfo->course = $this->arguments[0];
        $moduleinfo->coursemodule = 0;

        //choose random section from a course
        $sections = $DB->get_records('course_sections', array('course' => $this->arguments[0]), '', 'section');

        $moduleinfo->section = array_rand($sections);

        $moduleinfo->module = 12;
        $moduleinfo->modulename = 'label';
        $moduleinfo->instance = 0;
		// added fxp section
        $moduleinfo->section = $this->expandedOptions['section'];
        $moduleinfo->completion = $this->expandedOptions['completion'];
        $moduleinfo->add = 'label';
        $moduleinfo->update = 0;
        $moduleinfo->return = 0;
        $moduleinfo->sr = 0;



        $course = $DB->get_record('course', array('id' => $this->arguments[0]), '*', MUST_EXIST);
        add_moduleinfo($moduleinfo, $course);
    }
}


