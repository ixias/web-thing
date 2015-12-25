<?php




function snag_sql_query( $tree, $search ){
    if( isset($search) ) $data = mysqli_query($tree, "SELECT * FROM nodes WHERE connections LIKE '".$search."'");
    else $data = mysqli_query($tree, "SELECT * FROM nodes");
    $result = array();
    while( $info = mysqli_fetch_array( $data, MYSQLI_ASSOC ) ){
        $key = array_push( $result, array() ) - 1;
        $result[$key]["id"] = $info['id'];
        $result[$key]["title"] = $info['title'];
        $result[$key]["description"] = $info['description'];
        $result[$key]["body"] = $info['body'];
        $result[$key]["images"] = $info['images'];
        $result[$key]["connections"] = $info['connections'];
    }
    return $result;
}




function addChildren( $groups, $query ){
   $children = array();
    foreach( $groups as $details ){
        if( isset($details["group"]) && $details["group"] == $query ){
            $key = array_push($children,$details);
            $children[$key-1]["children"] = addChildren( $groups, $details["id"] );
        }
    }
    return $children;
}


function buildTree( $tree, $q ){
    $test = snag_sql_query($tree, $q);
    if( !empty($test) )
        foreach( $test as $nid => $testy )
            $test[$nid]["children"] = buildTree($tree,$testy["id"]);
    return $test;
}




function drillTree( $tree, $q ){
    foreach( $tree as $leaf ){
        if( $leaf["id"] == $q ) $gotit = $leaf;
        elseif( !empty($leaf["children"]) ) $gotit = drillTree( $leaf["children"], $q );
    }
    return $gotit;
}




function printTree( $tree, $depth="", $edit=0, $request="", $in_grp=array() ){
    echo( "<ul>\n" );
    foreach( $tree as $key => $leaf ){

        if( $leaf["id"] == $request ) echo( "<li class=\"active\">" );
        else echo( "<li>" );



        if( $edit ){
            echo( "<input type=\"checkbox\" id=\"".$leaf["id"]."\" name=\"".$leaf["id"]."\" class=\"connection\"/>\n" );
            echo( "<label for=\"".$leaf["id"]."\">".$leaf["title"]." (".$leaf["description"].")</label>\n" );
        }else{
            if(isset($leaf["path"])) $path = "/".$leaf["path"];
            else $path = $depth."/".$leaf["id"];
            echo( "  <a href=\"".$path."\">".$leaf["title"]."</a>" );
        }


        if( isset($leaf["children"]) )
            printTree( $leaf["children"], $path, $edit, $request, $in_grp );


        //START: PRINT ITEMS IN GROUP
        if( count($in_grp) ){
            echo( "<ul class=\"in-this-group\">\n" );
            for( $x=0; $x<count($in_grp); $x++ ){
                if( $in_grp[$x]["group"] == $leaf["id"] ){
                    echo( " <li>\n" );
                    echo( "  <a href=\"/node/".$in_grp[$x]["id"]."\">" );
                    echo( "   <span class=\"title\">".$in_grp[$x]["title"]."</span>\n" );
                    echo( "  </a>\n" );
                    echo( " </li>\n" );
                }
            }
            echo( "</ul>\n\n" );
        }
        //END: PRINT ITEMS IN GROUP


        echo( " </li>\n" );
    }
    echo( "</ul>\n" );
}




/*function print_flat_list_of_nested_children( $tree ){
    foreach( $tree["children"] as $child ){
        echo( "<span style=\"display:inline-block; padding:10px;border:1px solid red;\">\n" );
        if( !empty($child["children"]) )
            print_flat_list_of_nested_children( $child );
        echo( "</span>\n" );
    }
}*/




function printTable( $tree ){
    $full_table = snag_sql_query( $tree );
    print "<table>\n";
    echo( " <thead>\n" );
    print "  <tr>\n";
    print "   <th>ID</th>\n";
    print "   <th>Title</th>\n";
    print "   <th>Description</th>\n";
    #print "   <th>Body</th>\n";
    print "   <th>Images</th>\n";
    print "   <th>Connections</th>\n";
    print "   <th>Edit</th>\n";
    print "  </tr>\n";
    echo( " </thead>\n" );
    echo( " <tbody>\n" );
    for( $i = 0; $i<count($full_table); $i++ ){
        if( $i%2 == 0) echo( "<tr class=\"even\">\n" );
        else echo( "<tr class=\"odd\">\n" );
        print "  <td class=\"id\"><a href=\"/".$full_table[$i]['id']."\">" . $full_table[$i]['id'] . "</a></td>\n";
        print "  <td>" . $full_table[$i]['title'] . "</td>\n";
        print "  <td>" . $full_table[$i]['description'] . "</td>\n";
        #print "  <td>" . $full_table[$i]['body'] . "</td>\n";
        print "  <td>" . $full_table[$i]['images'] . "</td>\n";
        print "  <td>" . $full_table[$i]['connections'] . "</td>\n";
        print "  <td class=\"edit\"><a href=\"?edit=".$full_table[$i]['id']."\">EDIT</a></td>\n";
        print " </tr>\n";
    }
    echo( " </tbody>\n" );
    print "</table>\n";
}




function printCloud( $tree ){
    $full_table = snag_sql_query( $tree );
    shuffle( $full_table );
    echo( "<ul id=\"cloud\">\n" );
    for( $i = 0; $i<count($full_table); $i++ ){
        $item_uses = snag_sql_query( $tree, $full_table[$i]['id'] );
        $uses_class = "cloud_uses_few";
        if( count($item_uses) > 0 ) $uses_class = "cloud_uses_some";
        if( count($item_uses) > 1 ) $uses_class = "cloud_uses_many";
        echo( " <li class=\"".$uses_class."\">\n" );
        print "  <a href=\"/".$full_table[$i]['id']."\" title=\"".$full_table[$i]['description']."\">" . $full_table[$i]['id'] . "</a>\n";
        #$full_table[$i]['title']
        #$full_table[$i]['images']
        #$full_table[$i]['connections']
        print " </li>\n";
    }
    echo( "</ul>\n\n" );
}




function printGrid( $tree ){
    $full_table = snag_sql_query( $tree );
    echo( "<ul id=\"grid\">\n" );
    for( $i = 0; $i<count($full_table); $i++ ){
        $item_uses = snag_sql_query( $tree, $full_table[$i]['id'] );
        echo( " <li>\n" );
        echo( "  <a href=\"/".$full_table[$i]['id']."\" title=\"".$full_table[$i]['description']."\">" );
        echo( $full_table[$i]['id'] );
        echo( "<span class=\"uses\">Uses: ".count($item_uses)."</span>\n" );
        echo( "</a>\n" );
        #$full_table[$i]['title']
        #$full_table[$i]['images']
        #$full_table[$i]['connections']
        echo( " </li>\n" );
    }
    echo( "</ul>\n\n" );
}




function make_edits( $tree ){
    if( $_POST["addnode"] == "true" ){
        $addnd = "INSERT INTO nodes (id, title, description, body, images, connections)
                  VALUES ('".$_POST["id"]."', '".$_POST["title"]."', '".$_POST["description"]."', '".$_POST["body"]."', '".$_POST["images"]."', '".$_POST["connections"]."')";
        mysqli_query( $tree, $addnd );
        return "<div class=\"feedback\">Node Added</div>\n";
    }
    elseif( $_POST["edit"] != "" ){
        $editnd = "UPDATE nodes
                   SET id='".$_POST["id"]."', title='".$_POST["title"]."', description='".$_POST["description"]."', body='".$_POST["body"]."', images='".$_POST["images"]."', connections='".$_POST["connections"]."'
                   WHERE id='".$_POST["edit"]."'";
        mysqli_query( $tree, $editnd );
        return "<div class=\"feedback\">Node Edited</div>\n";
    }
}




function show_add_form( $test ){
    if( $_GET["edit"] == "new" ){
        echo( "<form method=\"POST\" action=\"/\">\n" );
        echo( " <div><input type=\"text\" id=\"id\" name=\"id\" placeholder=\"id\"/></div>\n" );
        echo( " <div><input type=\"text\" id=\"title\" name=\"title\" placeholder=\"title\"/></div>\n" );
        echo( " <div><input type=\"text\" id=\"description\" name=\"description\" placeholder=\"description\"/></div>\n" );
        echo( " <div><textarea id=\"body\" name=\"body\" placeholder=\"body\"></textarea></div>\n" );
        echo( " <div><input type=\"text\" id=\"images\" name=\"images\" placeholder=\"images\"/></div>\n" );
        echo( "<div id=\"tree\">\n" );
        printTree($test,"/",1);
        echo( "</div>\n\n" );
        echo( "<div><input type=\"text\" id=\"connections\" name=\"connections\" placeholder=\"connections\"/></div>\n" );
        echo( " <input type=\"hidden\" id=\"addnode\" name=\"addnode\" value=\"true\"/>\n" );
        echo( " <div><input type=\"submit\" value=\"Add\"/></div>\n" );
        echo( "</form>\n" );
    }
}




function printEditor( $ndid, $tree, $test ){
    $data = mysqli_query($tree, "SELECT * FROM nodes WHERE id='".$ndid."'");
    $info = mysqli_fetch_array( $data, MYSQLI_ASSOC );
    echo( "<form method=\"POST\" action=\"/\">\n" );
    echo( " <div><input type=\"text\" id=\"id\" name=\"id\" placeholder=\"id\" value=\"".$info["id"]."\"/></div>\n" );
    echo( " <div><input type=\"text\" id=\"title\" name=\"title\" placeholder=\"title\" value=\"".$info["title"]."\"/></div>\n" );
    echo( " <div><input type=\"text\" id=\"description\" name=\"description\" placeholder=\"description\" value=\"".$info["description"]."\"/></div>\n" );
    echo( " <div><textarea id=\"body\" name=\"body\" placeholder=\"body\">".$info["body"]."</textarea></div>\n" );
    echo( " <div><input type=\"text\" id=\"images\" name=\"images\" placeholder=\"images\" value=\"".$info["images"]."\"/></div>\n" );
    echo( "<div id=\"tree\">\n" );
    printTree($test,"/",1);
    echo( "</div>\n\n" );
    echo( " <div><input type=\"text\" id=\"connections\" name=\"connections\" placeholder=\"connections\" value=\"".$info["connections"]."\"/></div>\n" );
    echo( " <input type=\"hidden\" id=\"edit\" name=\"edit\" value=\"".$ndid."\"/>\n" );
    echo( " <div><input type=\"submit\" value=\"Save\"/></div>\n" );
    echo( "</form>\n" );
}




?>
<?php




$hidden_hash_var='K2JSHD4KFJH7KJDS7';

$LOGGED_IN=FALSE;
// Clear it out in case someone sets it in the URL or something
unset($LOGGED_IN);





function loggedin(){

	global $user_name, $id_hash, $hidden_hash_var, $LOGGED_IN;

	// Have we already run the hash checks? 
	// If so, return the pre-set var
	if(isset($LOGGED_IN)){

        return $LOGGED_IN;

	}
	elseif(isset($_COOKIE['user_name']) && $_COOKIE['id_hash']){

		$hash=md5($_COOKIE['user_name'].$hidden_hash_var);

		if($hash==$_COOKIE['id_hash']){
			$LOGGED_IN=TRUE;
			return $LOGGED_IN;
		}
		else{
			$LOGGED_IN=FALSE;
			return $LOGGED_IN;
		}

	}
	else{

		$LOGGED_IN=FALSE;
		return $LOGGED_IN;

	}
}








function login($userArray,$pass){

	if($pass==$userArray['password']){

		return TRUE;

	}

	return FALSE;

}










function user($userName,$accountDir) {
	if (file_exists($accountDir.'/index.xml')) {
		$userbaseIndex = new DOMDocument();
		if (!$userbaseIndex->load($accountDir.'/index.xml')) {
			echo('<div class="error">Could not open accounts file</div>');
			exit;
		}
		$userbaseIndex = $userbaseIndex->getElementsByTagName('index')->item(0);
		foreach ($userbaseIndex->childNodes as $user_entry) {
			if ($user_entry->nodeType == 1 && $user_entry->nodeName == 'item') {
				if ($userName == $user_entry->getAttribute('user')) {
					if (file_exists($accountDir.'/'.$user_entry->getAttribute('data'))) {
						$user_data = new DOMDocument();
						if (!$user_data->load($accountDir.'/'.$user_entry->getAttribute('data'))) {
							echo('	<p>Could not open account file.</p>');
							exit;
						}
						$user_data = $user_data->getElementsByTagName('user')->item(0);
						$user['username'] = $user_data->getAttribute('username');
						$user['password'] = $user_data->getAttribute('password');
						$user['email'] = $user_data->getAttribute('email');
						$user['admin'] = $user_data->getAttribute('admin');
						$user['publicprofile'] = $user_data->getAttribute('publicprofile');
						$user['realname'] = $user_data->getAttribute('realname');
						$user['location'] = $user_data->getAttribute('location');
						$user['publicemail'] = $user_data->getAttribute('publicemail');
						$user['homepage'] = $user_data->getAttribute('homepage');
						$user['icq'] = $user_data->getAttribute('icq');
						$user['aim'] = $user_data->getAttribute('aim');
						$user['msn'] = $user_data->getAttribute('msn');
						$user['yahoo'] = $user_data->getAttribute('yahoo');
						$user['photo'] = $user_data->getAttribute('photo');
						$user['update'] = $user_data->getAttribute('update');
						#$user['interests'] = $user_data->getElementsByTag('interests')->item(0)->textContent;
						return $user;
					}
				}
			}
		}
	}
}







function user_logout() {
	setcookie('user_name','',(time()+2592000),'/','',0);
	setcookie('id_hash','',(time()+2592000),'/','',0);
}







function user_set_tokens($user_name_in) {
	global $hidden_hash_var, $user_name, $id_hash;
	if (!$user_name_in) {
		$GLOBALS['feedback'].='ERROR - User Name Missing When Setting Tokens';
		return false;
	}
	$user_name = strtolower($user_name_in);
	$id_hash = md5($user_name.$hidden_hash_var);
	#setcookie('user_name',$user_name,(time()+2592000),'/','',0);
	#setcookie('id_hash',$id_hash,(time()+2592000),'/','',0);
	setcookie('user_name',$user_name,null,'/','',0);
	setcookie('id_hash',$id_hash,null,'/','',0);
	return true;
}
?>