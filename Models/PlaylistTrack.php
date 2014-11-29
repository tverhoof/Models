<?php

require_once('Base.php');
require_once('BaseException.php');
require_once('Playlist.php');
require_once('Track.php');

/**
* Class PlaylistTrack. It effectuates an active record on the table PlaylistTracks.
* 
*/

class PlaylistTrack
{
	
	// Attributes
	
	
	// The id of the Track in this PlaylistTrack.
	// @var small integer.
	
	private $track_id;
	
	// The id of the Playlist.
	// @var small integer.
	
	private $playlist_id;
	
	// The position of the Track in the Playlist.
	// @var small integer.
	
	private $position;
	
	
	// Requests
	
	
	private static $rq_SelectAll = "SELECT * FROM playlists_tracks ORDER BY playlist_id, position;";
	
	private static $rq_SelectViaTrackID = "SELECT * FROM playlists_tracks WHERE track_id=? ;";
	
	private static $rq_SelectViaPlaylistID = "SELECT * FROM playlists_tracks WHERE playlist_id=? ;";
	
	private static $rq_SelectPosition = "SELECT MAX(position) + 1 AS pos FROM playlists_tracks WHERE playlist_id = ?";
	
	private static $rq_Insertion = "INSERT INTO tracks(playlist_id, track_id, position)
		VALUES(:pl_id, :tr_id, :pos);";
		
	
	// Constructor
	
	
	/**
	* Creates a new instance of the class PlaylistTrack.
	* 
	* @return
	*/
	
	public function __construct() { }
	
	
	// Methods
	
	
	/**
	* Returns a string representation of this PlaylistTrack.
	* 
	* @return String A String representation of this PlaylistTrack.
	*/
	
	public function __toString()
	{
		$str = "[" . __CLASS__ . "]";
		
		if(isset($this->playlist_id))
		{
			$str .= " playlist_id : " . $this->playlist_id . ",";
		}
		
		if(isset($this->position))
		{
			$str .= " position : " . $this->position . ",";
		}
		
		if(isset($this->track_id))
		{
			$str .= " track_id : " . $this->track_id;
		}
		
		return $str . " . <br />";
	}
	
	
	// Getters and setters
	
	
	/** 
	* Gives acces to the attributes.
	* @param String $attr_name
	* 
	* @return the attribute which name was set in parameter if it 
	* exists, an Exception is thrown otherwise.
	*/
	
	public function __get($attr_name) 
	{
		if (property_exists( __CLASS__, $attr_name)) 
		{ 
		  return $this->$attr_name;
		}
		
		$emess = __CLASS__ . ": unknown member $attr_name (getAttr)";
		throw new Exception($emess, 45);
	}
	
	/**
	* Allows to modify the playlist_id of this PlaylistTrack. Its id must exist in the database.
	* @param int $nv_id
	* 
	* @return
	*/
	
	public function setPlaylistID($nv_id)
	{
		$nv_id = intval($nv_id);
	
		if (Playlist::CheckPlaylistExists($nv_id))
		{
			$this->playlist_id = $nv_id;
		}
		else
		{
			throw new Exception("La playlist n'existe pas dans la base de donnees.");
		}
	}
	
	/**
	* Allows to modify the track_id of this PlaylistTrack. Its id must exist in the database.
	* @param int $nv_id
	* 
	* @return
	*/
	
	public function setTrackID($nv_id)
	{
		$nv_id = intval($nv_id);
	
		if (Track::CheckTrackExists($nv_id))
		{
			$this->track_id = $nv_id;
		}
		else
		{
			throw new Exception("Le morceau n'existe pas dans la base de donnees.");
		}
	}
	

	// Delete, Insert et Update
	
	
	/**
	* Deletes this PlaylistTrack from the database.
	* 
	* @return
	*/
	
	public function delete()
	{
		echo 'Fonctionnalité ne nécessitant pas encore d\'être implémentée.<br />';
	}
	
	/**
	* Inserts this PlaylistTrack into the database, giving it its position in the Playlist.
	* 
	* @return
	*/
	
	public function insert()
	{		
		try
		{
			// Connection a la base.
	
			$bdd = Base::getConnection();
			
			// On gere si la designation ou le photopath n'est pas assignee.
			
			if ((!isset($this->playlist_id)) || (!isset($this->track_id)))
			{
				throw new Exception("playlist_id et track_id sont des champs obligatoires pour que le PlaylistTrack puisse etre insere dans la base de donnees.");
			}
			
			$this->position = $this->GetPosition();
			
			// On prépare la requete
			
			$requete = $bdd -> prepare(self::$rq_Insertion);
			$requete -> execute(array
			(
				'pl_id' => $this->playlist_id,
				'tr_id' => $this->track_id,
				'pos' => $this->position
			));	
		}
		catch(BaseException $e)
		{
			print $e -> getMessage();
		}
	}
	
	/**
	* Sets this PlaylistTrack up to date.
	* 
	* @return
	*/
	
	public function update()
	{
		echo 'Fonctionnalité ne nécessitant pas encore d\'être implémentée.<br />';
	}
	
	
	// Finders
	
	
	/**
	* Retrieves all PlaylistTracks in the database. 
	* @return An array of PlaylistTracks.
	*/
	
	public static function findAll()
	{
		try 
		{		
			// Connection a la base.
	
			$bdd = Base::getConnection();
			
			// On prépare la récupération de la table Moments.
			
			$requete = $bdd -> prepare(self::$rq_SelectAll);
			$requete->execute();
			
			// On transforme le résultat en un tableau d'objets
			
			$reponse = $requete->fetchALL(PDO::FETCH_OBJ);
			
			// Que l'on va retransformer en tableau de membres.
			
			$tab = array();
			$i = 0;
			
			foreach($reponse as $pl_trk)
			{
				$pt = new PlaylistTrack();
				
				$pt->playlist_id = $pl_trk->playlist_id;
				$pt->track_id = $pl_trk->track_id;
				$pt->position = $pl_trk->position;
				
				$tab[$i] = $pt;
				$i++;
			}
			
			$requete->closeCursor();
			return $tab;
		}
		catch(BaseException $e) { print $e -> getMessage(); }
	}
	
	/**
	* Retrieves a PlaylistTrack through its track_id.
	* @param integer $id
	* 
	* @return a PlaylistTrack, or null if not found.
	*/
	
	public static function findByTrackID($id)
	{
		try 
		{		
			// Connection a la base.
	
			$bdd = Base::getConnection();
			
			// On prépare la récupération du Membre avec l'ID spécifié.
			
			$requete = $bdd -> prepare(self::$rq_SelectViaTrackID);
			$requete->execute(array($id));
			
			// On transforme le résultat en un tableau d'objets
			
			$reponse = $requete->fetchALL(PDO::FETCH_OBJ);
			
			// Que l'on va retransformer en tableau de membres.
			
			$tab = array();
			$i = 0;
			
			foreach($reponse as $pl_trk)
			{
				$pt = new PlaylistTrack();
				
				$pt->playlist_id = $pl_trk->playlist_id;
				$pt->track_id = $pl_trk->track_id;
				$pt->position = $pl_trk->position;
				
				$tab[$i] = $pt;
				$i++;
			}
			
			$requete->closeCursor();
			return $tab;
		}
		catch(BaseException $e) { print $e -> getMessage(); }
	}
	
	/**
	* Retrieves a PlaylistTrack through its playlist_id.
	* @param integer $id
	* 
	* @return a PlaylistTrack, or null if not found.
	*/
	
	public static function findByPlaylistID($id)
	{
		try 
		{		
			// Connection a la base.
	
			$bdd = Base::getConnection();
			
			// On prépare la récupération du Membre avec l'ID spécifié.
			
			$requete = $bdd -> prepare(self::$rq_SelectViaPlaylistID);
			$requete->execute(array($id));
			
			// On transforme le résultat en un tableau d'objets
			
			$reponse = $requete->fetchALL(PDO::FETCH_OBJ);
			
			// Que l'on va retransformer en tableau de membres.
			
			$tab = array();
			$i = 0;
			
			foreach($reponse as $pl_trk)
			{
				$pt = new PlaylistTrack();
				
				$pt->playlist_id = $pl_trk->playlist_id;
				$pt->track_id = $pl_trk->track_id;
				$pt->position = $pl_trk->position;
				
				$tab[$i] = $pt;
				$i++;
			}
			
			$requete->closeCursor();
			return $tab;
		}
		catch(BaseException $e) { print $e -> getMessage(); }
	}
	
	
	// Other methods
	
	
	/**
	* Gets the position of the Track in the Playlist that
	* we want to insert. 
	* 
	* @return
	*/
	
	public function GetPosition()
	{
		try
		{
			// Connection a la base.
		
			$bdd = Base::getConnection();
			
			// On compte le nombre d'instance de PlaylistTrack avec cette playlist_id
			// pour obtenir la nouvelle position du morceau dans la Playlist.
			
			$reponse = $bdd -> prepare(self::$rq_SelectPosition);
			
			if(isset($this->playlist_id))
			{
				$num = $this->playlist_id;
			}
			else
			{
				$num = 0;
			}
			
			$reponse -> execute(array($num));
			
			// On récupere le résultat
			
			$result = $reponse -> fetch();
			$reponse -> closeCursor();
			
			if (!isset($result['pos'])) // Si MAX = 0
				return 1;
			else return $result['pos'];
		}
		catch(BaseException $e)
		{
			print $e -> getMessage();
		}
	}
}

?>