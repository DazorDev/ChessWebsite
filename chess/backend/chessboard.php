<?php

require "ChessPiece.php";
require "connect.php";
require_once "matchmaking.php";

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
    $array = json_decode(readFromDB($game)['boardState']);
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
    return mysqli_fetch_array($result);
}

function updateGameData($user, $game, $gameData) {
    global $connection;
    $boardState = json_encode($gameData);
    mysqli_query($connection, "INSERT INTO turn (player, game, boardState) VALUES('$user', '$game', '$boardState')");
}

function sendUpdate($game) {
    $turnData = readFromDB($game);
    $gameData = getGameState($game);
    checkForDeadKing($game, $gameData['black'], $gameData['white']);
    $state = $gameData['running'] == 0 ? 'game_over' : 'playing';
    $winner = $gameData['winner'] == $gameData['white'] ? 'white' : 'black';
    $currentPlayer = $gameData['white'] == $turnData['player'] ? "White" : "Black";
    if (!$turnData) {
        echo json_encode(array('success' => true, 'error' => true));
        return;
    }
    echo json_encode(array('success' => true, 'gameData' => $turnData['boardState'], 'state' => $state, 'winner' => $winner, 'currentPlayer' => $currentPlayer));
}

function checkForDeadKing($game, $black_user, $white_user) {
    $turnData = readFromDB($game);
    $board = json_decode($turnData['boardState']);
    $kings = [];
    foreach ($board as $piece) {
        if ($piece->type == 'king') $kings[] = $piece;
    }
    if (count($kings) < 2) {
        if ($kings[0]->color == 'black') {
            putWinner($game, $black_user);
        } else {
            putWinner($game, $white_user);
        }
        endMatch(array('id' => $game));
    }
}

function putWinner($game, $id) {
    global $connection;
    $query = "UPDATE game SET winner = '$id' WHERE id = '$game'";
    mysqli_query($connection, $query);
}

function getLastTurnUser($game) {
    global $connection;
    $query = "SELECT * FROM turn where game = '$game' ORDER BY id DESC LIMIT 1";
    $result = mysqli_query($connection, $query);
    return mysqli_fetch_array($result)['player'];
}

function getGameState($game) {
    global $connection;
    $query = "SELECT * FROM game where id = '$game' ORDER BY id DESC LIMIT 1";
    $result = mysqli_query($connection, $query);
    return mysqli_fetch_array($result);
}

//sendUpdate(24);
