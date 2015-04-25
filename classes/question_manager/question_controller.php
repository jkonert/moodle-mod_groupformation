<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.
/**
 * get questions
*
* @package mod_groupformation
* @author  Nora Wester
* @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
// 	require_once(dirname(__FILE__).'/storage_manager.php');
// 	require_once(dirname(__FILE__).'/xml_loader.php');
	require_once($CFG->dirroot.'/mod/groupformation/classes/moodle_interface/storage_manager.php');
	require_once($CFG->dirroot.'/mod/groupformation/classes/util/xml_loader.php');
//	require_once($CFG->dirroot.'/mod/groupformation/classes/util/define_file.php');

	if (!defined('MOODLE_INTERNAL')) {
		die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
	}

	define('MOTIVATION', 7);
	define('TEAM', 4);
	define('LEARNING', 6);
	define('CHARACTER', 5);
	define('GENERAL', 2);
	define('KNOWLEDGE', 1);
	define('TOPIC', 0);
	define('GRADE', 3);
	
	class mod_groupformation_question_controller {
		
// 		private $MOTIVATION = 7;
// 		private $TEAM = 4;
// 		private $LEARNING = 6;
// 		private $CHARACTER = 5;
// 		private $GENERAL = 2;
// 		private $KNOWLEDGE = 1;
// 		private $TOPIC = 0;
// 		private $GRADE = 3;
		
		private $SAVE = 0;
		private $COMMIT = 1;
		
		private $status;
		
		private $numbers = array();
		private $names = array('topic', 'knowledge', 'general', 'grade','team', 'character', 'learning', 'motivation');
	//	private $names = array();
		
		private $groupformationid;
		private $store;
		private $xml;
		private $szenario;
		private $lang;
		
		private $userId;
		
		private $currentCategoryPosition = 0;
		private $numberOfCategory;
	//	private $currentCategory;
		
		public function __construct($groupformationid, $lang, $userId){
			$this->groupformationid = $groupformationid;
			$this->lang = $lang;
			$this->userId = $userId;
			$this->store = new mod_groupformation_storage_manager($groupformationid);
			$this->xml = new mod_groupformation_xml_loader();
			//$this->names = CATEGORY_NAMES;
			$this->numberOfCategory = count($this->names);
			$this->init($userId);
		}
		
		private function init($userId){
			
			$this->szenario = $this->store->getSzenario();
			
			//var_dump($this->szenario);
			if(!$this->store->catalogTableNotSet()){
				$this->numbers = $this->store->getNumbers($this->names);
				$this->setNulls();
			}
			
			$this->status = $this->store->answeringStatus($userId);
			
		}
		
		private function setNulls(){
			if($this->szenario == 'project'){
				$this->numbers[LEARNING] = 0;
			}
				
			if($this->szenario == 'homework'){
				$this->numbers[MOTIVATION] = 0;
			}	
			
			if($this->szenario == 'presentation'){
				for($i = 0; $i < count($this->numbers); $i++){
					if($i != TOPIC && $i != KNOWLEDGE){
						$this->numbers[$i] = 0;
					}
				}
			}
			
			var_dump($this->numbers);
		}
		
		public function hasNext(){
			
			if($this->currentCategoryPosition > -1 && $this->currentCategoryPosition < $this->numberOfCategory){
// 				var_dump($this->numbers[$this->currentCategoryPosition] == 0);
// 				var_dump('hasNext');
				while($this->currentCategoryPosition < $this->numberOfCategory && $this->numbers[$this->currentCategoryPosition] == 0){
					$this->currentCategoryPosition++;
				}
				
				//var_dump($this->currentCategoryPosition);
				
// 				if($this->numbers[$this->currentCategoryPosition] == 0){
			
// 					var_dump('h');
// 					$this->currentCategoryPosition++;
// 				}
			}
			return ($this->currentCategoryPosition != -1 && $this->currentCategoryPosition < $this->numberOfCategory);
		}
		
		public function getNextQuestion(){
			
			if($this->currentCategoryPosition != -1){
			
				$questions = array();
				
				if($this->currentCategoryPosition == TOPIC || $this->currentCategoryPosition == KNOWLEDGE){
					
 						$temp = $this->store->getDozentQuestion($this->names[$this->currentCategoryPosition]);
 						//var_dump($temp);
						$values = $this->xml->xmlToArray('<?xml version="1.0" encoding="UTF-8" ?> <OPTIONS> ' . $temp . ' </OPTIONS>');
						
						$text; 
						$type;
 						if($this->currentCategoryPosition == TOPIC){
 							if($this->lang == 'de'){
 								$text = 'Wie gef�llt Ihnen das Thema ';
 							}else{
 								$text = 'How much you like the topic ';
 							}
 							$type = 'typThema';
 						}else{
 							if($this->lang == 'de'){
 								$text = 'Wie sch�tzen Sie Ihr Vorwissen im folgenden Bereich ein:';
 							}else{
 								$text = 'How you rate your knowledge in ';
 							}
 							$type = 'typVorwissen';
 						}
 						
 						//TODO @JK/Eduard K�nnen wir es hier mit dem Optionarray so lasen, oder sollen wir ein anders nehmen/gar keins?
 						$options = array();
 						if($this->lang == 'de'){
 							$options = array('sehr gut', '', '', '','gar nicht');
 						}else{
 							$options = array('excellent', '', '', '','none');
 						}
 						
 						foreach ($values as $value){
 							$question = array();
 							$question[] = $type;
 							$question[] = $text . $value;
 							$question[] = $options;
							$questions[] = $question;
						}
					
						
					
				}else{
				
					for($i = 1; $i <= $this->numbers[$this->currentCategoryPosition]; $i++){
						$array = $this->store->getCatalogQuestion($i, $this->names[$this->currentCategoryPosition], $this->lang);
						$question = array();
						$question[] = $array->type;
						$question[] = $array->question;
						$o = $array->options;
						
						$question[] = $this->xml->xmlToArray('<?xml version="1.0" encoding="UTF-8" ?> <OPTIONS> ' . $o . ' </OPTIONS>');
						//$questions[] = $array->options;
						$questions[] = $question;
					}
				}		
				//hier wird schon die neue Kategory angesetzt
				//deswegen muss beim holen der Answers zur�ck gerechnet werden
				$this->currentCategoryPosition++;
				
				return $questions;
			}
		}
		
		// von jeder Frage muss auch eine Antwort im array vorhanden sein
		public function saveAnswers(array $answers){
			$temp = 1;
			foreach($answers as $answer){
				$this->store->saveAnswer($this->userId, $answer, $this->names[$this->currentCategoryPosition-1], $temp);
				$temp++;
			}
			
			if($this->status == -1){
				$this->status = $this->SAVE;
				$this->store->statusChanged($this->userId);
			}
		}
		
// 		private function getNextCategory(){
// 			if($this->szenario == 'project'){
// 				if($this->currentCategoryPostiton == $this->LEARNING){
// 					$this->currentCategoryPosition++;
// 				}
// 			}
			
// 			if($this->szenario == 'homework'){
// 				if($this->currentCategoryPostiton == $this->MOTIVATION){
// 					$this->currentCategoryPosition++;
// 				}
// 			}
			
// 			//TODO das ist die eigentliche Abfrage; nur solange bis die anderen Datenbanken voll sind
// // 			if($this->currentCategoryPosition == 6 || $this->szenario == 'presentation'){
// // 				$this->currentCategoryPosition = -1;
// // 			}

// 			if($this->currentCategoryPosition == 7 || $this->szenario == 'presentation'){
// 				$this->currentCategoryPosition = -1;
//  			}
// 		}
		
		public function questionsToAnswer(){
			return $this->store->answeringStatus($this->userId) != 1;
		}
		
		public function hasAnswers(){
			$firstCondition = $this->store->answeringStatus($this->userId) == 0;
			//var_dump($this->names[$this->currentCategoryPosition-1]);
			$secondCondition = $this->store->answerExist($this->userId, $this->names[$this->currentCategoryPosition-1], 1);
// 			var_dump($secondCondition);
// 			var_dump($firstCondition);
// 			var_dump($firstCondition && $firstCondition);
			return ($firstCondition && $secondCondition);
		}
		
		public function getAnswers(){
			$array = array();
			
			$answers = $this->store->getAnswer($this->userId, $this->names[$this->currentCategoryPosition-1]);
			//var_dump($answers);
			foreach($answers as $answer){
				$temp = array(
						'position' => $answer->questionid,
						'answer' => $answer->answer
				);
				
				$array[] = $temp;
			}
			
			return $array;
		}
		
		public function getCurrentCategory(){
			return $this->names[$this->currentCategoryPosition];
		}
		
		public function commited(){
			$this->store->statusChanged($this->userId);
		}
// 		public function saveFirstAnswers($userId, array $answers){
// 			$temp = 0;
// 			foreach($answers as $answer){
// 				$this->store->saveAnswer($userId, $answer, 'general', $temp);
// 				$temp++;
// 			}
// 		}
		
// 		public function getFirstQuestion($userId){
			
// 			$answerStatus = $this->store->answeringStatus($userId);
			
// 			if($this->store->existSetting() && $this->currentCategoryPosition == 0 && $answerStatus != 1){
			
// 				$number = $this->store->firstQuestionNumber();
			
// 				$questions = array();
			
// 				if($number > 0){
// 					for($i = 1; $i <= $number; $i++){
// 						$array = $this->store->getQuestion($i);
// 						$questions[] = $array->type;
// 						$questions[] = $array->question;
// 						//$o = $array->options;
// 						//$questions[] = $this->xml->xmlToArray('<OPTIONS>' . $o . '</OPTIONS>');
// 						$questions[] = $array->options;
// 					}
// 				}
			
// 				//var_dump($questions);
// 				$this->currentCategoryPosition++;
// 				return $questions;
// 			}
// 		}
	}