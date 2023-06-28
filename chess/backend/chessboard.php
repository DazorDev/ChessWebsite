<?php

require "ChessPiece.php";
require "connect.php";

/*
    SAMPLE FORMAT
    {
        Piece : {
            id : something,
            x : something,
            y : something,
            type : something,
            color : something
        },
    }
    if piece is captured it should be removed from this array
*/

function getGameData($game) {
    $array = json_decode(readFromDB($game));
    $phpArray = [];
    foreach ($array as $piece) {
        $phpArray[] = new Piece($piece->id, $piece->x, $piece->y, $piece->type, $piece->color);
    }
    return $phpArray;
}

function getPiece($game, $id, $color) {
    foreach ($game as $piece) {
        if ($piece->id != $id) continue;
        if ($piece->color != $color) continue;
        return $piece;
    }
    return null;
}

function getPieceFromPos($board, $position) {
    foreach ($board as $piece) {
        if ($position->x != $piece->x) continue;
        if ($position->y != $piece->y) continue;
        return $piece;
    }
    return null;
}

function readFromDB($game) {
    global $connection;
    $result = mysqli_query($connection, "SELECT * FROM turn WHERE game = '$game' ORDER BY id DESC");
    return mysqli_fetch_array($result)['boardState'];
}

function updateGameData($game, $user, $gameData) {
    global $connection;
    $boardState = json_encode($gameData);
    mysqli_query($connection, "INSERT INTO turn (game, player, boardState) VALUES('$user', '$game', '$boardState')");
}

function sendUpdate($game) {
    $data = readFromDB($game);
    if (!$data) {
        echo json_encode(array('success' => true, 'error' => true));
        return;
    }
    echo json_encode(array('success' => true, 'gameData' => $data));
}

//sendUpdate(24);
