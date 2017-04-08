<?php
/******************************************************

  This file is part of OpenWebSoccer-Sim.

  OpenWebSoccer-Sim is free software: you can redistribute it 
  and/or modify it under the terms of the 
  GNU Lesser General Public License 
  as published by the Free Software Foundation, either version 3 of
  the License, or any later version.

  OpenWebSoccer-Sim is distributed in the hope that it will be
  useful, but WITHOUT ANY WARRANTY; without even the implied
  warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
  See the GNU Lesser General Public License for more details.

  You should have received a copy of the GNU Lesser General Public 
  License along with OpenWebSoccer-Sim.  
  If not, see <http://www.gnu.org/licenses/>.

******************************************************/

/**
 * Provides players of own team which are lended to other teams.
 */
class LentPlayersModel implements IModel {
	private $_db;
	private $_i18n;
	private $_websoccer;
	
	public function __construct($db, $i18n, $websoccer) {
		$this->_db = $db;
		$this->_i18n = $i18n;
		$this->_websoccer = $websoccer;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see IModel::renderView()
	 */
	public function renderView() {
		return TRUE;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see IModel::getTemplateParameters()
	 */
	public function getTemplateParameters() {
		
		$teamId = $this->_websoccer->getUser()->getClubId($this->_websoccer, $this->_db);

		$columns = array(
				"P.id" => "id",
				"vorname" => "firstname",
				"nachname" => "lastname",
				"kunstname" => "pseudonym",
				"picture" => "picture",
				"position" => "position",
				"position_main" => "position_main",
				"position_second" => "position_second",
				"marktwert" => "marketvalue",
				'vertrag_spiele' => 'contract_matches',
				'vertrag_gehalt' => 'contract_salary',				
				"nation" => "player_nationality",
				"P.sa_spiele" => "st_matches",
				"P.sa_tore" => "st_goals",
				"P.sa_assists" => "st_assists",
				"P.note_last" => "note_last",
				"P.note_schnitt" => "note_schnitt",
				"lending_matches" => "lending_matches",
				"lending_fee" => "lending_fee",
				"lending" => "lending",
				"comment" => "comment",
				"clausel" => "clausel",
				"C.id" => "team_id",
				"C.name" => "team_name"
		);
		
		if ($this->_websoccer->getConfig('players_aging') == 'birthday') {
			$ageColumn = 'TIMESTAMPDIFF(YEAR,geburtstag,CURDATE())';
		} else {
			$ageColumn = 'age';
		}
		$columns[$ageColumn] = 'age';
		
		$dbPrefix = $this->_websoccer->getConfig("db_prefix");
		$fromTable = $dbPrefix . "_spieler P INNER JOIN " . $dbPrefix . "_verein C ON C.id = P.verein_id";
		$whereCondition = "P.status = 1 AND lending_owner_id = %d";
		
		$whereCondition .= " ORDER BY lending ASC, position ASC, position_main ASC, nachname ASC, vorname ASC";
		
		$result = $this->_db->querySelect($columns, $fromTable, $whereCondition, $teamId, 50);
		
		$players = array();
		while ($player = $result->fetch_array()) {
			$player["position"] = PlayersDataService::_convertPosition($player["position"]);
			$player['player_nationality_filename'] = self::getFlagFilename($player['player_nationality']);
			$players[] = $player;
		}
		$result->free();
		
		return array('lentplayers' => $players);
	}

	/**
	 * Provides the correct flag file name for specified nationality.
	 * Removes umlauts.
	 * 
	 * @param string $nationality
	 * @return string fag file name.
	 */
	public static function getFlagFilename($nationality) {
		if (!strlen($nationality)) {
			return $nationality;
		}
		
		// remove umlauts
		$filename = str_replace('??', 'Ae', $nationality);
		$filename = str_replace('??', 'Oe', $filename);
		$filename = str_replace('??', 'Ue', $filename);
		
		$filename = str_replace('??', 'ae', $filename);
		$filename = str_replace('??', 'oe', $filename);
		$filename = str_replace('??', 'ue', $filename);
		return $filename;
	}	
	
}

?>