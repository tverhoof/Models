<?php

require_once('Base.php');
require_once('BaseException.php');
require_once('Track.php');

/**
* Class Artist. It effectuates an active record on the table Artists.
* 
*/

class Artist
{
	
	// Attributs
	
	
	// The id of this Artist
	// @var small integer.
	
	private $artist_id;
	
	// The name of this Artist.
	// @var string(30)
	
	private $name;
	
	// The url from where an image of this Artist can be loaded.
	// @var string(255)
	
	private $image_url;
	
	// Some information about this Artist.
	// @var textarea
	
	private $info;
	
	// The list of Title created by this Artist. Null if not loaded,
	// an array of Title otherwise.
	// @var array.
	
	private $titres;
	
	// The regex checking if the image_url is valid.
	
	private static $image_regex = "##";
	
	
	// Requests
	
	
	private static $rq_SelectAll = "SELECT * FROM artists ORDER BY name;";
	
	private static $rq_SelectViaID = "SELECT * FROM artists WHERE artist_id=? ;";
	
	private static $rq_SelectViaName = "SELECT * FROM artists WHERE name=? ;";
	
	private static $rq_SelectViaLike = "SELECT * FROM artists WHERE name LIKE ? ORDER BY name;";
							
	private static $rq_Insertion = "INSERT INTO artists(name, image_url, info)
		VALUES(:name, :url, :info);";
	
	private static $rq_SelectExistsName = "SELECT Count(*) AS copies FROM artists WHERE 
	LOWER(name) = :name AND artist_id <> :id;";
	
	private static $rq_SelectExistsArtist = "SELECT Count(*) AS copies FROM artists WHERE artist_id = :id;";
	
	private static $rq_SelectTitres = "SELECT * FROM Track_V WHERE artist_id = ?;";
	
	
	// Constructor
	
	
	/**
	* Creates a new instance of the class Artist.
	* 
	* @return
	*/
	
	public function __construct() { }
	
	
	// Methods
	
	
	/**
	* Returns a string representation of this Artist.
	* 
	* @return String A String representation of this Artist.
	*/
	
	public function __toString()
	{
		$str = "[" . __CLASS__ . "]";
		
		if(isset($this->artist_id))
		{
			$str .= " artist_id : " . $this->artist_id . ",";
		}
		
		if(isset($this->name))
		{
			$str .= " name : " . $this->name . ",";
		}
		
		if(isset($this->image_url))
		{
			$str .= " image_url : " . $this->image_url . ",";
		}
		
		if(isset($this->info))
		{
			$str .= " info : " . $this->info;
		}
		
		if(isset($this->titres))
		{
			$str .= " titres : " . var_dump($this->titres);
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
	* Allows to modify the name of this Artist. It must at least have 2 chars, max 30, 
	* and must be unique(including caps).
	* @param String $nv_nom
	* 
	* @return
	*/
	
	public function setName($nv_nom)
	{
		if(strlen($nv_nom) > 1)
		{
			if(strlen($nv_nom) < 31)
			{
				if($this->CheckNameUnique($nv_nom))
				{
					$this->name = $nv_nom;
				}
				else
				{
					throw new Exception("Le nom que vous souhaitez donner a votre
					artiste existe deja dans la base de donnees.");
				}			
			}
			else
			{
				throw new Exception("Le nom que vous souhaitez donner a votre
					artiste est trop long. Le maximum étant de 30 caracteres.");
			}
		}
		else
		{
			throw new Exception("Le nom que vous souhaitez donner a votre
				artiste est trop court. Le minimum étant de 2 caracteres.");
		}
	}
	
	/**
	* Allows to modify the ImageUrl of this Artist. It has to pass the ImageRegex.
	* @param String $nv_image_url
	* 
	* @return
	*/
	
	public function setImageUrl($nv_image_url)
	{
		if(preg_match(Artist::$image_regex, $nv_image_url))
		{
			$this->image_url = $nv_image_url;
		}
		else
		{
			throw new Exception("Le lien ne pointe pas vers une image.");
		}
	}
	
	/**
	* Allows to modify the informations about of this Artist.
	* @param String $nv_info
	* 
	* @return
	*/
	
	public function setInfo($nv_info)
	{	
		$this->info = $nv_info;
	}
	

	// Delete, Insert et Update
	
	
	/**
	* Deletes this Artist from the database.
	* 
	* @return
	*/
	
	public function delete()
	{
		echo 'Fonctionnalité ne nécessitant pas encore d\'être implémentée.<br />';
	}
	
	/**
	* Inserts this Artist into the database and retrieves its artist_id.
	* 
	* @return The artist_id of the inserted Artist, null otherwise.
	*/
	
	public function insert()
	{		
		try
		{
			// Connection a la base.
	
			$bdd = Base::getConnection();
			
			// On gere si la designation ou le photopath n'est pas assignee.
			
			if (!isset($this->name))
			{
				throw new Exception("L'artiste n'a pas pu etre insere dans la base de donnees car le nom de l'artiste n'a pas ete specifie et il s'agit d'un champ obligatoire.");
			}
			
			// On prépare la requete
			
			$requete = $bdd -> prepare(self::$rq_Insertion);
			$requete -> execute(array
			(
				'name' => $this->name,
				'url' => $this->image_url,
				'info' => $this->info
			));
			
			// On récupere l'identifiant du Membre inséré.
			
			$this->artist_id = $bdd->LastInsertID('artists');
			$requete->closeCursor();
			return $this->artist_id;		
		}
		catch(BaseException $e)
		{
			print $e -> getMessage();
		}
	}
	
	/**
	* Sets this Artist up to date.
	* 
	* @return
	*/
	
	public function update()
	{
		echo 'Fonctionnalité ne nécessitant pas encore d\'être implémentée.<br />';
	}
	
	
	// Finders
	
	
	/**
	* Retrieves all Artiss in the database. 
	* @return An array of Artists.
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
			
			foreach($reponse as $art)
			{
				$a = new Artist();
				
				$a->artist_id = $art->artist_id;
				$a->name = $art->name;
				$a->image_url = $art->image_url;
				$a->info = $art->info;
				
				$tab[$i] = $a;
				$i++;
			}			
			
			$requete->closeCursor();
			return $tab;
		}
		catch(BaseException $e) { print $e -> getMessage(); }
	}
	
	/**
	* Retrieves an Artist through its artist_id.
	* @param integer $id
	* 
	* @return an Artist, or null if not found.
	*/
	
	public static function findByID($id)
	{
		try 
		{		
			// Connection a la base.
	
			$bdd = Base::getConnection();
			
			// On prépare la récupération du Membre avec l'ID spécifié.
			
			$requete = $bdd -> prepare(self::$rq_SelectViaID);
			$requete->execute(array($id));
			
			// On transforme le résultat en un objet
			
			$reponse = $requete->fetch(PDO::FETCH_ASSOC);
			
			// On transforme l'objet en un membre
			
			if($reponse)
			{
				$a = new Artist();
				$a->artist_id = $reponse['artist_id'];
				$a->name = $reponse['name'];
				$a->image_url = $reponse['image_url'];
				$a->info = $reponse['info'];
				$requete->closeCursor();			
				return $a;
			}
			else return null;
		}
		catch(BaseException $e) { print $e -> getMessage(); }
	}
	
	/**
	* Retrieves an Artist through its name.
	* @param String $nom
	* 
	* @return an Artist, or null if not found.
	*/
	
	public static function findByName($nom)
	{
		try 
		{		
			// Connection a la base.
	
			$bdd = Base::getConnection();
			
			// On prépare la récupération du Membre avec le nom spécifié.
			
			$requete = $bdd -> prepare(self::$rq_SelectViaName);
			$requete->execute(array($nom));
			
			// On transforme le résultat en un objet
			
			$reponse = $requete->fetch(PDO::FETCH_ASSOC);
			
			// On transforme l'objet en un membre
			
			if($reponse)
			{
				$a = new Artist();
				$a->artist_id = $reponse['artist_id'];
				$a->name = $reponse['name'];
				$a->image_url = $reponse['image_url'];
				$a->info = $reponse['info'];
				$requete->closeCursor();			
				return $a;
			}
			else return null;
		}
		catch(BaseException $e) { print $e -> getMessage(); }
	}
	
	/**
	* Retrieves an array of all Artists which name's contains
	* a specified sequence of characters.
	* @param String $nom The specified sequence of characters.
	* 
	* @return an array of all Artists which name's contains
	* a specified sequence of characters.
	*/
	
	public static function findByNameLike($nom)
	{
		try 
		{		
			// Connection a la base.
	
			$bdd = Base::getConnection();
			
			// On prépare la récupération du Membre avec le nom spécifié.
			
			$requete = $bdd -> prepare(self::$rq_SelectViaLike);
			$requete->execute(array("%" . $nom . "%"));
			
			// On transforme le résultat en un tableau d'objets
			
			$reponse = $requete->fetchALL(PDO::FETCH_OBJ);
			
			// Que l'on va retransformer en tableau de membres.
			
			$tab = array();
			$i = 0;
			
			foreach($reponse as $art)
			{
				$a = new Artist();
				
				$a->artist_id = $art->artist_id;
				$a->name = $art->name;
				$a->image_url = $art->image_url;
				$a->info = $art->info;
				
				$tab[$i] = $a;
				$i++;
			}			
			
			$requete->closeCursor();
			return $tab;
		}
		catch(BaseException $e) { print $e -> getMessage(); }
	}
	
	/**
	* Loads the Tracks created by this Artist into the titles attribute.
	* Doesn't function if artist_id isn't set.
	*  
	* @return
	*/
	
	public function chargerTitres()
	{
		try 
		{	
			// Vérification que l'artist possede son ID.
			
			if(!isset($this->artist_id))
			{
				return;
			}
		
			// Connection a la base.
	
			$bdd = Base::getConnection();
			
			// On prépare la récupération du Membre avec le nom spécifié.
			
			$requete = $bdd -> prepare(self::$rq_SelectTitres);
			$requete->execute(array($this->artist_id));
			
			// On transforme le résultat en un tableau d'objets
			
			$reponse = $requete->fetchALL(PDO::FETCH_OBJ);
			
			// Que l'on va retransformer en tableau de membres.
			
			$tab = array();
			$i = 0;
			
			foreach($reponse as $trk)
			{
				$tab[$i] = Track::CreateFromFetchObj($trk);
				$i++;
			}			
			
			$requete->closeCursor();
			$this->titres = $tab;
		}
		catch(BaseException $e) { print $e -> getMessage(); }
	
	}
	
	// Other methods
	
	
	/**
	* Checks if a name is truly unique(caps included).
	* @param String $nom
	* 
	* @return true if the name is unique, false otherwise.
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
			
			// On récupere le résulat
			
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
	* @return true if the artist_id specified as parameter is found in
	* the database, false otherwise.
	*/
	
	public static function CheckArtistExists($id)
	{
		try
		{
			// Connection a la base.
		
			$bdd = Base::getConnection();
			
			// On prépare la requete pour compter le nombre de nom équivalent 
			// au parametre
			
			$reponse = $bdd -> prepare(self::$rq_SelectExistsArtist);			
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