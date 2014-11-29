<?php

require_once('Base.php');
require_once('BaseException.php');
require_once('Playlist.php');

/**
* Class User. It effectuates an active record on the table Users.
* 
*/

class User
{
	
	// Attributes
	
	
	// The ID of this User.
	// @var small integer.
	
	private $user_id;
	
	// The name of this User.
	// @var string(??)
	
	private $username;
	
	// The password (hashed ?) of this User
	// @var string(255)
	
	private $password;
	
	// The email of this User.
	// @var string(255)
	
	private $email;
	
	// The list of Playlist created by this User. Null if not loaded,
	// an array of Playlist otherwise.
	// @var array
	
	private $playlists;
	
	// The regex checking if the mail address is valid.
	
	private static $email_regex = "#^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$#";
	
	
	// Requests
	
	
	private static $rq_SelectAll = "SELECT * FROM users ORDER BY username;";
	
	private static $rq_SelectViaID = "SELECT * FROM users WHERE user_id=? ;";
	
	private static $rq_SelectViaUsername = "SELECT * FROM users WHERE username=? ;";
							
	private static $rq_Insertion = "INSERT INTO users(username, password, email)
		VALUES(:name, :pass, :mail);";
	
	private static $rq_SelectExistsUsername = "SELECT Count(*) AS copies FROM users WHERE 
	LOWER(username) = :name AND user_id <> :id;";
	
	private static $rq_SelectExistsEmail = "SELECT Count(*) AS copies FROM users WHERE 
	email = :mail AND user_id <> :id;";
	
	private static $rq_SelectExistsUserID = "SELECT Count(*) AS copies FROM users WHERE 
	user_id = :id;";
	
	private static $rq_SelectAuthentification = "SELECT * FROM users WHERE username=? AND password=?;";
	
	
	// Constructor
	
	
	// Creates a new instance of the User class.
	
	public function __construct() { }
	
	
	// Methods
	
		
	/**
	* Returns a string representation of this User.
	* 
	* @return String A String representation of this User.
	*/
	
	public function __toString()
	{
		$str = "[" . __CLASS__ . "]";
		
		if(isset($this->user_id))
		{
			$str .= " user_id : " . $this->user_id . ",";
		}
		
		if(isset($this->username))
		{
			$str .= " username : " . $this->username . ",";
		}
		
		if(isset($this->password))
		{
			$str .= " password : " . $this->password . ",";
		}
		
		if(isset($this->email))
		{
			$str .= " email : " . $this->email . ",";
		}
		
		if(isset($this->playlists))
		{
			$str .= " playlists : " . $this->playlists;
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
	* Allows to modify the username. It must at least have 2 chars, max 15, 
	* and must be unique(including caps).
	* @param String $nv_nom 
	* 
	* @return
	*/
	
	public function setUsername($nv_nom)
	{
		if(strlen($nv_nom) > 1)
		{
			if(strlen($nv_nom) < 16)
			{
				if($this->CheckUsernameUnique($nv_nom))
				{
					$this->username = $nv_nom;
				}
				else
				{
					throw new Exception("Le nom que vous souhaitez donner a votre
					membre n'est pas unique...");
				}			
			}
			else
			{
				throw new Exception("Le nom que vous souhaitez donner a votre
					membre est trop long. Le maximum étant de 15 caracteres.");
			}
		}
		else
		{
			throw new Exception("Le nom que vous souhaitez donner a votre
				membre est trop court. Le minimum étant de 2 caracteres.");
		}
	}
	
	/**
	* Allows to modify the password.
	* 
	* @param String $nv_pass
	* 
	* @return
	*/
	
	public function setPassword($nv_pass)
	{
		$this->password = $nv_pass;
	}
	
	/**
	* Alloes to modify the email. 255 chars max, and has to be unique
	* and to pass the email_regex.
	* @param String $nv_email
	* 
	* @return
	*/
	
	public function setEmail($nv_email)
	{	
		if(strlen($nv_email) < 256)
		{
			if(preg_match(User::$email_regex, $nv_email))
			{
				if(User::CheckEmailUnique($nv_email))
				{
					$this->email = $nv_email;
				}
				else
				{
					throw new Exception("Un compte existe deja avec cette adresse mail.");
				}
			}
			else
			{
				throw new Exception("L'adresse mail donné ne correspond pas a une vraie adresse.");
			}
		}
		else
		{
			throw new Exception("La désignation est trop longue. Le maximum est de 255 caracteres.");
		}
	}
	

	// Delete, Insert and Update
	
		
	/**
	* Deletes this User from the database.
	* 
	* @return
	*/
	
	public function delete()
	{
		echo 'Fonctionnalité ne nécessitant pas encore d\'être implémentée.<br />';
	}
	
	/**
	* Inserts this User into the database and retrieves its user_id.
	* 
	* @return The user_id of the inserted User, null otherwise.
	*/
	
	public function insert()
	{		
		try
		{
			// Connection a la base.
	
			$bdd = Base::getConnection();
			
			// On gere si la designation ou le photopath n'est pas assignee.
			
			if((!isset($this->username)) || (!isset($this->password)) || (!isset($this->email)))
			{
				throw new Exception("Toutes les informations necessaires n'ont pas été données.");
			}
			
			// On prépare la requete
			
			$requete = $bdd -> prepare(self::$rq_Insertion);
			$requete -> execute(array
			(
				'name' => $this->username,
				'pass' => $this->password,
				'mail' => $this->email
			));
			
			// On récupere l'identifiant du Membre inséré.
			
			$this->user_id = $bdd->LastInsertID('users');
			$requete->closeCursor();
			return $this->user_id;		
		}
		catch(BaseException $e)
		{
			print $e -> getMessage();
		}
	}
	
	/**
	* Sets this User up to date.
	* 
	* @return
	*/
	
	public function update()
	{
		echo 'Fonctionnalité ne nécessitant pas encore d\'être implémentée.<br />';
	}
	
	// Finders
	
	
	/**
	* Retrieves all Users in the database. 
	* @return An array of Users.
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
			
			foreach($reponse as $us)
			{
				$u = new User();
				$u->user_id = $us->user_id;
				$u->username = $us->username;
				$u->password = $us->password;
				$u->email = $us->email;
				$tab[$i] = $u;
				$i++;
			}			
			
			$requete->closeCursor();			
			return $tab;
		}
		catch(BaseException $e) { print $e -> getMessage(); }
	}
	
	/**
	* Retrieves an User through its user_id.
	* @param integer $id
	* 
	* @return an User, or null if not found.
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
				$u = new User();
				$u->user_id = $reponse['user_id'];
				$u->username = $reponse['username'];
				$u->password = $reponse['password'];
				$u->email = $reponse['email'];
				$requete->closeCursor();			
				return $u;
			}
			else return null;
		}
		catch(BaseException $e) { print $e -> getMessage(); }
	}
	
	/**
	* Retrieves a User through its username.
	* @param String $nom
	* 
	* @return a User, or null if not found.
	*/
	
	public static function findByName($nom)
	{
		try 
		{		
			// Connection a la base.
	
			$bdd = Base::getConnection();
			
			// On prépare la récupération du Membre avec le nom spécifié.
			
			$requete = $bdd -> prepare(self::$rq_SelectViaUsername);
			$requete->execute(array($nom));
			
			// On transforme le résultat en un objet
			
			$reponse = $requete->fetch(PDO::FETCH_ASSOC);
			
			// On transforme l'objet en un membre
			
			if ($reponse)
			{
				$u = new User();
				$u->user_id = $reponse['user_id'];
				$u->username = $reponse['user_id'];
				$u->password = $reponse['password'];
				$u->email = $reponse['email'];
				$requete->closeCursor();
				return $u;
			}
			else return null;
		}
		catch(BaseException $e) { print $e -> getMessage(); }
	}
	
	/**
	* Loads the playlists created by this User and inserts them
	* in the playlists attributes as an Array of Playlists.
	* Fails if (!isset(this->user_id)).
	* 
	* @return
	*/
	
	public function chargerPlaylists()
	{
		if(!isset($this->user_id))
			return;
			
		$this->playlists = Playlist::findByUser($this->user_id);
	}
	
	// Other methods
	
		
	// Vérifie qu'un nom est bel est bien unique (maj. comprises).
	// Vrai si le nom est unique, faux sinon.
	
	/**
	* Checks if the specified name already exists in the database.
	* @param String $nom
	* 
	* @return true if the specified name doesn't exists in the database, false otherwise.
	*/
	
	public function CheckUsernameUnique($nom)
	{
		try
		{
			// Connection a la base.
		
			$bdd = Base::getConnection();
			
			// On prépare la requete pour compter le nombre de nom équivalent 
			// au parametre
			
			$reponse = $bdd -> prepare(self::$rq_SelectExistsUsername);
			
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
	* Checks if the specified id already exists in the database.
	* @param String $id
	* 
	* @return true if the specified id exists in the database, false otherwise.
	*/
	
	public static function CheckUserIDExists($id)
	{
		try
		{
			// Connection a la base.
		
			$bdd = Base::getConnection();
			
			// On prépare la requete pour compter le nombre de nom équivalent 
			// au parametre
			
			$reponse = $bdd -> prepare(self::$rq_SelectExistsUserID);
			
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
	
	/**
	* Checks if the specified mail address already exists in the database.
	* @param String $nom
	* 
	* @return true if the specified mail address doesn't exists in the database, false otherwise.
	*/
	
	public function CheckEmailUnique($nom)
	{
		try
		{
			// Connection a la base.
		
			$bdd = Base::getConnection();
			
			// On prépare la requete pour compter le nombre de nom équivalent 
			// au parametre
			
			$reponse = $bdd -> prepare(self::$rq_SelectExistsEmail);
			
			if(isset($this->user_id))
			{
				$num = $this->user_id;
			}
			else
			{
				$num = 0;
			}
			
			$reponse -> execute(array(
			'mail' => $nom,
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
	* Tries to authificate a User by checking if the 
	* specified username and password matches.
	* @param String $nom The name of the User wo wants to connect.
	* @param String $pass The password of the User wo wants to connect.
	* 
	* @return The User if the authentification was successful, null other.
	*/
	
	public static function Authentification($nom, $pass)
	{
		try 
		{		
			// Connection a la base.
	
			$bdd = Base::getConnection();
			
			// On prépare la récupération du Membre avec l'ID spécifié.
			
			$requete = $bdd -> prepare(self::$rq_SelectAuthentification);
			$requete->execute(array($nom, $pass));
			
			// On transforme le résultat en un objet
			
			$reponse = $requete->fetch(PDO::FETCH_ASSOC);
			
			// On transforme l'objet en un membre
			
			if($reponse)
			{
				$u = new User();
				$u->user_id = $reponse['user_id'];
				$u->username = $nom;
				$u->password = $pass;
				$u->email = $reponse['email'];
				$requete->closeCursor();			
				return $u;
			}
			else return null;
		}
		catch(BaseException $e) { print $e -> getMessage(); }
	}
}

?>