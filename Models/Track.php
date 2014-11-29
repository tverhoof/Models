<?php

require_once('Base.php');
require_once('BaseException.php');
require_once('Artist.php');

/**
* Class Track. It effectuates an active record on the table Tracks.
*
* 
*/

class Track
{
	
	// Attributes
	
	
	// The id of this Track.
	// @var small integer.
	
	private $track_id;
	
	// The id of the Artist who created this Track.
	// @var small integer.
	
	private $artist_id;
	
	// The title of this Track
	// @var string(50).
	
	private $title;
	
	// An url refering to this Track in mp3-format.
	// @var string(255)
	
	private $mp3_url;
	
	// The name of the Artist who created this Track.
	// @var string(30)
	
	private $artist_name;
	
	// The regex checking if the mp3 link is valid..
	
	private static $mp3_regex = "#^[a-z0-9_-\s]+.mp3$#";
	
	
	// Requests
	
	
	private static $rq_SelectAll = "SELECT * FROM Track_V ORDER BY title;";
	
	private static $rq_SelectViaTrackID = "SELECT * FROM Track_V WHERE track_id=? ;";
	
	private static $rq_SelectViaArtistID = "SELECT * FROM Track_V WHERE artist_id=? ORDER BY title;";
	
	private static $rq_SelectViaNameLike = "SELECT * FROM Track_V WHERE title LIKE ? ORDER BY title;";
	
	private static $rq_SelectViaArtistAndNameLike = "SELECT * FROM Track_V WHERE title LIKE :name AND artist_id= :id ORDER BY title;";
	
	private static $rq_Insertion = "INSERT INTO tracks(artist_id, title, mp3_url)
		VALUES(:id, :title, :url);";
	
	private static $rq_SelectExistsName = "SELECT Count(*) AS copies FROM tracks WHERE 
	LOWER(title) = :name AND artist_id = :id;";
	
	private static $rq_SelectExistsTrack = "SELECT Count(*) AS copies FROM tracks WHERE track_id = :id;";
	
	
	// Constructor
	
	
	/**
	* Creates a new instance of the class Track.
	* 
	* @return
	*/
	
	public function __construct() { }
	
	
	// Methods
	
	
	/**
	* Returns a string representation of this Track.
	* 
	* @return String A String representation of this Track.
	*/
	
	public function __toString()
	{
		$str = "[" . __CLASS__ . "]";
		
		if(isset($this->track_id))
		{
			$str .= " track_id : " . $this->track_id . ",";
		}
		
		if(isset($this->artist_id))
		{
			$str .= " artist_id : " . $this->artist_id . ",";
		}
		
		if(isset($this->title))
		{
			$str .= " title : " . $this->title . ",";
		}
		
		if(isset($this->mp3_url))
		{
			$str .= " mp3_url : " . $this->mp3_url . ",";
		}
		
		if(isset($this->artist_name))
		{
			$str .= " artist_name : " . $this->artist_name . ",";
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
	* Allows to modify the id of this Artist who created this Track. Its id must exist in the database.
	* @param int $nv_id
	* 
	* @return
	*/
	
	public function setArtistID($nv_id)
	{
		$nv_id = intval($nv_id);
	
		if (Artist::CheckArtistExists($nv_id))
		{
			$this->artist_id = $nv_id;
		}
		else
		{
			throw new Exception("L'artiste_id n'existe pas dans la base de donnees.");
		}
	}
	
	/**
	* Allows to modify the name of this Title. Its title must be unique in all other title
	* of the Artist who created it, so the artist_id must be set. It must at least have 2 chars, max 50. 
	* @param String(50) $nv_nom
	* 
	* @return
	*/
	
	public function setTitle($nv_nom)
	{
		if (!isset($this->artist_id))
		{
			throw new Exception("Artist_id doit etre indique pour pouvoir modifier le titre.");
		}
	
		if(strlen($nv_nom) > 1)
		{
			if(strlen($nv_nom) < 51)
			{
				if($this->CheckNameUnique($nv_nom))
				{
					$this->title = $nv_nom;
				}
				else
				{
					throw new Exception("L'artiste a deja un titre avec ce nom.");
				}			
			}
			else
			{
				throw new Exception("Le nom que vous souhaitez donner a votre
					titre est trop long. Le maximum étant de 50 caracteres.");
			}
		}
		else
		{
			throw new Exception("Le nom que vous souhaitez donner a votre
				titre est trop court. Le minimum étant de 2 caracteres.");
		}
	}
	
	/**
	* Allows to modify the mp3 url of this Track. It must match with the Mp3Regex.
	* @param String $nv_nom
	* 
	* @return
	*/
	
	public function setMp3Url($nv_url)
	{
		if(preg_match(Track::$mp3_regex, $nv_url))
		{
			$this->mp3_url = $nv_url;
		}
		else
		{
			throw new Exception("Le lien ne pointe pas vers un fichier mp3.");
		}
	}
	

	// Delete, Insert et Update
	
	
	/**
	* Deletes this Track from the database.
	* 
	* @return
	*/
	
	public function delete()
	{
		echo 'Fonctionnalité ne nécessitant pas encore d\'être implémentée.<br />';
	}
	
	/**
	* Inserts this Track into the database and retrieves its track_id.
	* 
	* @return The track_id of the inserted Track, null otherwise.
	*/
	
	public function insert()
	{		
		try
		{
			// Connection a la base.
	
			$bdd = Base::getConnection();
			
			// On gere si la designation ou le photopath n'est pas assignee.
			
			if (!isset($this->title))
			{
				throw new Exception("Le morceau n'a pas pu etre insere dans la base de donnees car le titre n'a pas ete specifie et il s'agit d'un champ obligatoire.");
			}
			
			// On prépare la requete
			
			$requete = $bdd -> prepare(self::$rq_Insertion);
			$requete -> execute(array
			(
				'id' => $this->artist_id,
				'title' => $this->title,
				'url' => $this->mp3_url
			));
			
			// On récupere l'identifiant du Membre inséré.
			
			$this->track_id = $bdd->LastInsertID('tracks');
			$requete->closeCursor();
			return $this->track_id;		
		}
		catch(BaseException $e)
		{
			print $e -> getMessage();
		}
	}
	
	/**
	* Sets this Track up to date.
	* 
	* @return
	*/
	
	public function update()
	{
		echo 'Fonctionnalité ne nécessitant pas encore d\'être implémentée.<br />';
	}
	
	
	// Finders
	
	
	/**
	* Retrieves all Tracks in the database. 
	* @return An array of Tracks.
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
			
			foreach($reponse as $trk)
			{
				$t = new Track();
				
				$t->track_id = $trk->track_id;
				$t->artist_id = $trk->artist_id;
				$t->title = $trk->title;
				$t->mp3_url = $trk->mp3_url;
				$t->artist_name = $trk->name;
				
				$tab[$i] = $t;
				$i++;
			}			
			
			$requete->closeCursor();
			return $tab;
		}
		catch(BaseException $e) { print $e -> getMessage(); }
	}
	
	/**
	* Retrieves a Track through its track_id.
	* @param integer $id
	* 
	* @return a Track, or null if not found.
	*/
	
	public static function findByID($id)
	{
		try 
		{		
			// Connection a la base.
	
			$bdd = Base::getConnection();
			
			// On prépare la récupération du Membre avec l'ID spécifié.
			
			$requete = $bdd -> prepare(self::$rq_SelectViaTrackID);
			$requete->execute(array($id));
			
			// On transforme le résultat en un objet
			
			$reponse = $requete->fetch(PDO::FETCH_ASSOC);
			
			// On transforme l'objet en un membre
			
			if($reponse)
			{
				$t = new Track();
				
				$t->track_id = $reponse['track_id'];
				$t->artist_id = $reponse['artist_id'];
				$t->title = $reponse['title'];
				$t->mp3_url = $reponse['mp3_url'];
				$t->artist_name = $reponse['name'];
			
				$requete->closeCursor();			
				return $t;
			}
			else return null;
		}
		catch(BaseException $e) { print $e -> getMessage(); }
	}
	
	/**
	* Retrieves a Track through its artist_id.
	* @param integer $artist_id
	* 
	* @return a Track, or null if not found.
	*/
	
	public static function findByArtist($artist_id)
	{
		try 
		{		
			// Connection a la base.
	
			$bdd = Base::getConnection();
			
			// On prépare la récupération du Membre avec le nom spécifié.
			
			$requete = $bdd -> prepare(self::$rq_SelectViaArtistID);
			$requete->execute(array($artist_id));
			
			// On transforme le résultat en un tableau d'objets
			
			$reponse = $requete->fetchALL(PDO::FETCH_OBJ);
			
			// Que l'on va retransformer en tableau de membres.
			
			$tab = array();
			$i = 0;
			
			foreach($reponse as $trk)
			{
				$t = new Track();
				
				$t->track_id = $trk->track_id;
				$t->artist_id = $trk->artist_id;
				$t->title = $trk->title;
				$t->mp3_url = $trk->mp3_url;
				$t->artist_name = $trk->name;
				
				$tab[$i] = $t;
				$i++;
			}			
			
			$requete->closeCursor();
			return $tab;
		}
		catch(BaseException $e) { print $e -> getMessage(); }
	}
	
	/**
	* Retrieves an array of Track through a part of their title.
	* @param String $name
	* 
	* @return an array off all Tracks with $name in their title.
	*/
	
	public static function findByNameLike($name)
	{
		try 
		{		
			// Connection a la base.
	
			$bdd = Base::getConnection();
			
			// On prépare la récupération du Membre avec le nom spécifié.
			
			$requete = $bdd -> prepare(self::$rq_SelectViaNameLike);
			$requete->execute(array("%" . $name . "%"));
			
			// On transforme le résultat en un tableau d'objets
			
			$reponse = $requete->fetchALL(PDO::FETCH_OBJ);
			
			// Que l'on va retransformer en tableau de membres.
			
			$tab = array();
			$i = 0;
			
			foreach($reponse as $trk)
			{
				$t = new Track();
				
				$t->track_id = $trk->track_id;
				$t->artist_id = $trk->artist_id;
				$t->title = $trk->title;
				$t->mp3_url = $trk->mp3_url;
				$t->artist_name = $trk->name;
				
				$tab[$i] = $t;
				$i++;
			}			
			
			$requete->closeCursor();
			return $tab;
		}
		catch(BaseException $e) { print $e -> getMessage(); }
	}
	
	/**
	* Retrieves an array of Track through a part of their title,
	* and a specified Artist.
	* @param int $id The artist_id of the Artist who created the 
	* tracks we want to retrieve.
	* @param String $name
	* 
	* @return an array off all Tracks with $name in their title
	* and created by the specified Artist.
	*/
	
	public static function findByArtistAndNameLike($id, $name)
	{
		try 
		{		
			// Connection a la base.
	
			$bdd = Base::getConnection();
			
			// On prépare la récupération du Membre avec le nom spécifié.
			
			$requete = $bdd -> prepare(self::$rq_SelectViaArtistAndNameLike);
			$requete->execute(array(
			'name' => "%" . $name . "%",
			'id' => $id
			));
			
			// On transforme le résultat en un tableau d'objets
			
			$reponse = $requete->fetchALL(PDO::FETCH_OBJ);
			
			// Que l'on va retransformer en tableau de membres.
			
			$tab = array();
			$i = 0;
			
			foreach($reponse as $trk)
			{
				$t = new Track();
				
				$t->track_id = $trk->track_id;
				$t->artist_id = $trk->artist_id;
				$t->title = $trk->title;
				$t->mp3_url = $trk->mp3_url;
				$t->artist_name = $trk->name;
				
				$tab[$i] = $t;
				$i++;
			}			
			
			$requete->closeCursor();
			return $tab;
		}
		catch(BaseException $e) { print $e -> getMessage(); }
	}
	
	/**
	* Creates a Track from a PDO response.
	* @param PDO-Object $trk
	* 
	* @return a Track from a PDO response.
	*/
	
	public static function CreateFromFetchObj($trk)
	{
		$t = new Track();
				
		$t->track_id = $trk->track_id;
		$t->artist_id = $trk->artist_id;
		$t->artist_name = $trk->name;
		$t->title = $trk->title;
		$t->mp3_url = $trk->mp3_url;
		return $t;
	}
	
	
	// Other methods
	
	
	/**
	* Checks if a title is truly unique in all Tracks created
	* by the Artist. Throws an Exception if this artist_id 
	* isn't specified.
	* @param String $nom
	* 
	* @return true if the title is unique, false otherwise.
	*/
	
	public function CheckNameUnique($nom)
	{
		try
		{
			// Connection a la base.
		
			$bdd = Base::getConnection();
			
			// On prépare la requete pour compter le nombre de nom équivalent 
			// au parametre
			
			$reponse = $bdd -> prepare(self::$rq_SelectExistsName);
			
			if(isset($this->artist_id))
			{
				$num = $this->artist_id;
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
	* Checks if an Artist really exists in the database through its id.
	* @param int $id
	* 
	* @return true if the track_id specified as parameter is found in
	* the database, false otherwise.
	*/
	
	public static function CheckTrackExists($id)
	{
		try
		{
			// Connection a la base.
		
			$bdd = Base::getConnection();
			
			// On prépare la requete pour compter le nombre de nom équivalent 
			// au parametre
			
			$reponse = $bdd -> prepare(self::$rq_SelectExistsTrack);			
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