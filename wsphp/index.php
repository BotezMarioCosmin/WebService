<?php
// Connessione al database
$servername = "localhost";
$username = "programma";
$password = "12345";
$dbname = "digitalgamestore";

$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica della connessione
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

// Elabora header
$metodo = $_SERVER["REQUEST_METHOD"];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

if ($metodo == "GET") {
    if ($uri[3] == "prodotti") {
         if (count($uri) == 4 && $uri[3] == "prodotti" || count($uri) == 5 && $uri[4] == null) { // al quarto posto "prodotti"
            $sql = "SELECT Prodottoid, nome, prezzo, categoria, sviluppatore, pubblicatore FROM prodotti";
            $result = $conn->query($sql);
            
            if ($result->num_rows > 0) {
                $prodotti = array();
                while ($row = $result->fetch_assoc()) {
                    $prodotti[] = $row;
                }
                header("Content-Type: application/json");
                echo json_encode($prodotti);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Nessun prodotto trovato."));
            }
        } else if (count($uri) == 5) {
            // Se specificato un prodotto
            $prodotto_id = $uri[4];
            $sql = "SELECT Prodottoid, nome, prezzo, categoria, sviluppatore, pubblicatore FROM prodotti WHERE Prodottoid = '$prodotto_id'";
            $result = $conn->query($sql);
            
            if ($result->num_rows > 0) {
                $prodotto = $result->fetch_assoc();
                header("Content-Type: application/json");
                echo json_encode($prodotto);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Prodotto non trovato."));
            }
        }
    } else {
        // Percorso non valido
        http_response_code(404);
        echo json_encode(array("message" => "Percorso non valido."));
    }
} else {
    // Metodo non supportato
    http_response_code(405);
    echo json_encode(array("message" => "Metodo non supportato."));
}

if ($metodo == "POST") {
    // Recupera i dati dal corpo della richiesta
    $body = file_get_contents('php://input');
    
    // Decodifica i dati in un array associativo
    $data = json_decode($body, true);
    

    if ($uri[4] == "add") {

        $nome = $data['nome'];
        $prezzo = $data['prezzo'];
        $categoria = $data['categoria'];
        $sviluppatore = $data['sviluppatore'];
        $pubblicatore = $data['pubblicatore'];
        
        $sql = "INSERT INTO prodotti (nome, prezzo, categoria, sviluppatore, pubblicatore) 
                VALUES ('$nome', '$prezzo', '$categoria', '$sviluppatore', '$pubblicatore')";
        
        if ($conn->query($sql) === TRUE) {
            // Inserimento riuscito
            http_response_code(201); // Created
            echo json_encode(array("message" => "Prodotto aggiunto con successo."));
        } else {
            // In caso di errore nell'inserimento
            http_response_code(500); 
            echo json_encode(array("message" => "Errore durante l'aggiunta del prodotto: " . $conn->error));
        }
    } else {
        http_response_code(400); 
        echo json_encode(array("message" => "Il percorso richiesto non è valido per il metodo POST."));
    }
}



if ($metodo == "PUT") {

    if ($uri[4] == "put") {
       
        $prodotto_id = $uri[5];
        
        // Recupera i dati dal corpo della richiesta
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        
       
        $nome = $data['nome'];
        $prezzo = $data['prezzo'];
        $categoria = $data['categoria'];
        $sviluppatore = $data['sviluppatore'];
        $pubblicatore = $data['pubblicatore'];
        
        //query
        $sql = "UPDATE prodotti SET 
                nome = '$nome',
                prezzo = '$prezzo',
                categoria = '$categoria',
                sviluppatore = '$sviluppatore',
                pubblicatore = '$pubblicatore' 
                WHERE Prodottoid = '$prodotto_id'";
        

        if ($conn->query($sql) === TRUE) {
            // Aggiornamento riuscito
            http_response_code(200); // OK
            echo json_encode(array("message" => "Prodotto aggiornato con successo."));
        } else {
            //errore
            http_response_code(500); 
            echo json_encode(array("message" => "Errore durante l'aggiornamento del prodotto: " . $conn->error));
        }
    } else {

        http_response_code(400); 
        echo json_encode(array("message" => "Il percorso richiesto non è valido per il metodo PUT."));
    }
}


if ($metodo == "DELETE") {
    if ($uri[4] == "del") {
        $prodotto_id = $uri[5];
        if ($prodotto_id != null)
        {
            $sql = "DELETE FROM prodotti WHERE Prodottoid = '$prodotto_id'";
        
            if ($conn->query($sql) === TRUE) {
                // Eliminazione riuscita
                http_response_code(200); // OK
                echo json_encode(array("message" => "Prodotto eliminato con successo."));
            } else {
                //errore elim
                http_response_code(500);
                echo json_encode(array("message" => "Errore durante l'eliminazione del prodotto: " . $conn->error));
            }
        }
        else
        {
            // id non inserito
            http_response_code(400);
            echo json_encode(array("message" => "Id non inserito."));
        }

    } else {
        http_response_code(400); // Bad Request
        echo json_encode(array("message" => "Il percorso richiesto non è valido per il metodo DELETE."));
    }
}



$conn->close();
?>