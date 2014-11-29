<?php

require_once('Base.php');
require_once('BaseException.php');
require_once('User.php');
require_once('Track.php');

/**
* Class Playlist. It effectuates an active record on the table Playlists.
* 
*/

class Playlist
{
	
	// Attributes
	
	
	// The id of the user who created this Plyalist.
	// @var small integer.
	
	private $user_id;
	
	// The id of this Playlist.
	// @var small integer.
	
	private $playlist_id;
	
	// The name of this Playlist.
	// @var string(255)
	
	private $playlist_name;
	
	// The list of Track contained in this Playlist. Null if not loaded,
	// an array of Track otherwise with [0] => null, and [1] the track at
	// position one, if any. 
	// @var array.
	
	private $tracks;
	
	
	// Requests
	
	
	private static $rq_SelectAll = "SELECT * FROM playlists ORDER BY user_id, playlist_name;";
	
	private static $rq_SelectViaPlaylistID = "SELECT * FROM playlists WHERE playlist_id=? ;";
	
	private static $rq_SelectViaUserID = "SELECT * FROM playlists WHERE user_id=? ORDER BY playlist_name;";
	
	private static $rq_Insertion = "INSERT INTO playlists(user_id, playlist_name)
		VALUES(:user, :name);";
	
	private static $rq_SelectExistsName = "SELECT Count(*) AS copies FROM playlists WHERE 
	LOWER(playlist_name) = :name AND user_id = :id;";
	
	private static $rq_SelectExistsPlaylist = "SELECT Count(*) AS copies FROM playlists WHERE playlist_id = :id;";
	
	private static $rq_SelectTracks = "SELECT * FROM Playlist_V WHERE playlist_id = ? ORDER BY position;";
	
	
	// Constructor
	
	
	/**
	* Creates a new instance of the class Playlist.
	* 
	* @return
	*/
	
	public function __construct() { }
	
	
	// Methods
	
	
	/**
	* Returns a string representation of this Playlist.
	* 
	* @return String A String representation of this Playlist.
	*/
	
	public function __toString()
	{
		$str = "[" . __CLASS__ . "]";
		
		if(isset($this->user_id))
		{
			$str .= " user_id : " . $this->user_id . ",";
		}
		
		if(isset($this->playlist_id))
		{
			$str .= " playlist_id : " . $this->playlist_id . ",";
		}
		
		if(isset($this->playlist_name))
		{
			$str .= " playlist_name : " . $this->playlist_name;
		}
		
		if(isset($this->tracks))
		{
			$str .= " tracks : " . $this->tracks;
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
	* Allows to modify the id of this User who created this Playklist. Its id must exist in the database.
	* @param int $nv_id
	* 
	* @return
	*/
	
	public function setUserID($nv_id)
	{
		$nv_id = intval($nv_id);
	
		if (User::CheckUserIDExists($nv_id))
		{
			$this->user_id = $nv_id;
		}
		else
		{
			throw new Exception("L'user_id n'existe pas dans la base de donnees.");
		}
	}
	
	/**
	* Allows to modify the name of this Playlist. It must be unique for the User, and
	* so the user_id attribute has to be set.
	* @param String $nv_nom
	* 
	* @return
	*/
	
	public function setPlaylistName($nv_nom)
	{
		if (!isset($this->user_id))
		{
			throw new Exception("User_id doit etre indique pour pouvoir modifier le nom de la playlist.");
		}
	
		if(Playlist::CheckPlaylistNameUnique($nv_nom))
		{
			$this->playlist_name = $nv_nom;
		}
		else
		{
			throw new Exception("L'utilsateur a deja une playlist avec le ce nom.");
		}
	}
	

	// Delete, Insert et Update
	
	
	/**
	* Deletes this Playlist from the database.
	* 
	* @return
	*/
	
	public function delete()
	{
		echo 'Fonctionnalité ne nécessitant pas encore d\'être implémentée.<br />';
	}
	
	/**
	* Inserts this Playlist into the database and retrieves its playlist_id.
	* 
	* @return The playlist_id of the inserted Playlist, null otherwise.
	*/
	
	public function insert()
	{		
		try
		{
			// Connection a la base.
	
			$bdd = Base::getConnection();
			
			// On gere si la designation ou le photopath n'est pas assignee.
			
			if (!isset($this->playlist_name))
			{
				throw new Exception("La playlist n'a pas pu etre insere dans la base de donnees car le nom de la playlist n'a pas ete specifie et il s'agit d'un champ obligatoire.");
			}
			
			// On prépare la requete
			
			$requete = $bdd -> prepare(self::$rq_Insertion);
			$requete -> execute(array
			(
				'name' => $this->playlist_name,
				'user' => $this->user_id
			));
			
			// On récupere l'identifiant du Membre inséré.
			
			$this->playlist_id = $bdd->LastInsertID('playlists');
			$requete->closeCursor();
			return $this->playlist_id;		
		}
		catch(BaseException $e)
		{
			print $e -> getMessage();
		}
	}
	
	/**
	* Sets this Playlist up to date.
	* 
	* @return
	*/
	
	public function update()
	{
		echo 'Fonctionnalité ne nécessitant pas encore d\'être implémentée.<br />';
	}
	
	
	// Finders
	
	
	/**
	* Retrieves all Playlists in the database. 
	* @return An array of Playlists.
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
			
			foreach($reponse as $plist)
			{
				$pl = new Playlist();
				
				$pl->playlist_id = $plist->playlist_id;
				$pl->user_id = $plist->user_id;
				$pl->playlist_name = $plist->playlist_name;
				
				$tab[$i] = $pl;
				$i++;
			}			
			
			$requete->closeCursor();
			return $tab;
		}
		catch(BaseException $e) { print $e -> getMessage(); }
	}
	
	/**
	* Retrieves a Playlist through its playlist_id.
	* @param integer $id
	* 
	* @return a Playlist, or null if not found.
	*/
	
	public static function findByID($id)
	{
		try 
		{		
			// Connection a la base.
	
			$bdd = Base::getConnection();
			
			// On prépare la récupération du Membre avec l'ID spécifié.
			
			$requete = $bdd -> prepare(self::$rq_SelectViaPlaylistID);
			$requete->execute(array($id));
			
			// On transforme le résultat en un objet
			
			$reponse = $requete->fetch(PDO::FETCH_ASSOC);
			
			// On transforme l'objet en un membre
			
			if($reponse)
			{
				$pl = new Playlist();
				$pl->playlist_id = $reponse['playlist_id'];
				$pl->user_id = $reponse['user_id'];
				$pl->playlist_name = $reponse['playlist_name'];
				$requete->closeCursor();			
				return $pl;
			}
			else return null;
		}
		catch(BaseException $e) { print $e -> getMessage(); }
	}
	
	/**
	* Retrieves a Playlist through its user_id.
	* @param int $user_id
	* 
	* @return a Playlist, or null if not found.
	*/
	
	public static function findByUser($user_id)
	{
		try 
		{		
			// Connection a la base.
	
			$bdd = Base::getConnection();
			
			// On prépare la récupération du Membre avec le nom spécifié.
			
			$requete = $bdd -> prepare(self::$rq_SelectViaUserID);
			$requete->execute(array($user_id));
			
			// On transforme le résultat en un tableau d'objets
			
			$reponse = $requete->fetchALL(PDO::FETCH_OBJ);
			
			// Que l'on va retransformer en tableau de membres.
			
			$tab = array();
			$i = 0;
			
			foreach($reponse as $plist)
			{
				$pl = new Playlist();
				
				$pl->playlist_id = $plist->playlist_id;
				$pl->user_id = $plist->user_id;
				$pl->playlist_name = $plist->playlist_name;
				
				$tab[$i] = $pl;
				$i++;
			}			
			
			$requete->closeCursor();
			return $tab;
		}
		catch(BaseException $e) { print $e -> getMessage(); }
	}
	
	/**
	* Loads the Tracks in this Playlist into the titles attribute.
	* Doesn't function if playlist_id isn't set.
	*  
	* @return
	*/
	
	public function chargerTracks()
	{
		try 
		{	
			if(!isset($this->playlist_id))
				return;			
			
			// Connection a la base.
	
			$bdd = Base::getConnection();
			
			// On prépare la récupération du Membre avec le nom spécifié.
			
			$requete = $bdd -> prepare(self::$rq_SelectTracks);
			$requete->execute(array($this->playlist_id));
			
			// On transforme le résultat en un tableau d'objets
			
			$reponse = $requete->fetchALL(PDO::FETCH_OBJ);
			
			// Que l'on va retransformer en tableau de membres.
			
			$tab = array();
			$i = 0;
			
			foreach($reponse as $trk)
			{
				$t = Track::CreateFromFetchObj($trk);
				$tab[$trk->position] = $t;
			}
			
			$requete->closeCursor();
			$this->tracks = $tab;
		}
		catch(BaseException $e) { print $e -> getMessage(); }
	}
	
	
	// Other methods
	
	
	/**
	* Checks if the name of this Playlist is truly unique(caps included),
	* for its User. 
	* @param String $nom
	* 
	* @return true if the name is unique, false otherwise.
	*/
	
	public function CheckPlaylistNameUnique($nom)
	{
		try
		{
			// Connection a la base.
		
			$bdd = Base::getConnection();
			
			// On prépare la requete pour compter le nombre de nom équivalent 
			// au parametre
			
			$reponse = $bdd -> prepare(self::$rq_SelectExistsName);
			
			if(isset($this->user_id))
			{
				$num = $this->user_id;
			}
			else
			{
				$num = 0;
			}
			
			$reponse -> execute(array(
			'name' => strtolower($nom),
			'id' => $num
			));
			
			// On récupere le résultat
			
			$result = $reponse -> fetch();
			$reponse -> closeCursor();
			
			// Si le compte est 0, le nom est unique, on retourne vrai.
			
			return ($result['copies'] == 0);
		}
		catch(BaseException $e)
		{
			print $e -> getMessage();
		}
	}
	
	/**
	* Checks if a Playlist really exists in the database through its id.
	* @param int $id
	* 
	* @return true if the playlist_id specified as parameter is found in
	* the database, false otherwise.
	*/
	
	public static function CheckPlaylistExists($id)
	{
		try
		{
			// Connection a la base.
		
			$bdd = Base::getConnection();
			
			// On prépare la requete pour compter le nombre de nom équivalent 
			// au parametre
			
			$reponse = $bdd -> prepare(self::$rq_SelectExistsPlaylist);			
			$reponse -> execute(array('id' => $id));
			
			// On récupere le résulat
			
			$result = $reponse -> fetch();
			$reponse -> closeCursor();
			
			// Si le compte est 0, le nom est unique, on retourne vrai.
			
			return ($result['copies'] != 0);
		}
		catch(BaseException $e)
		{
			print $e -> getMessage();
		}
	}
}

?>