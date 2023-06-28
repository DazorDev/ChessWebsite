<?php

require "connect.php";

function searchMatch($id) {
    return "SELECT * FROM user WHERE id != '$id' AND searching = '1'";
}

function searchCreatedMatch($user) {
    return "SELECT * FROM game WHERE white = '$user' OR black = '$user' AND running = '1' ORDER BY id DESC LIMIT 1";
}

function createMatch($user1, $user2) {
    return "INSERT INTO game (black, white, running) VALUES('$user1', '$user2', '1')";
}

function insertUserIntoQueue($id) {
    return "UPDATE user SET searching = 1 WHERE id = '$id'";
}

function reqRemoveQueue($id) {
    return "UPDATE user SET searching = 0 WHERE id = '$id'";
}

function removeFromQueue($user) {
    callDB(reqRemoveQueue($user));
}

function deque($user1, $user2) {
    removeFromQueue($user1);
    removeFromQueue($user2);
}

function callDB($search) {
    global $connection;
    return mysqli_query($connection, $search);
}

function enterQueue($user) {
    callDB(insertUserIntoQueue($user));
}

function createStandardBoard() {
    return array(
        new Piece("pawn0", 0, 1, 'pawn', 'white'),
        new Piece("pawn1", 1, 1, 'pawn', 'white'),
        new Piece("pawn2", 2, 1, 'pawn', 'white'),
        new Piece("pawn3", 3, 1, 'pawn', 'white'),
        new Piece("pawn4", 4, 1, 'pawn', 'white'),
        new Piece("pawn5", 5, 1, 'pawn', 'white'),
        new Piece("pawn6", 6, 1, 'pawn', 'white'),
        new Piece("pawn7", 7, 1, 'pawn', 'white'),
        new Piece("pawn0", 0, 6, 'pawn', 'black'),
        new Piece("pawn1", 1, 6, 'pawn', 'black'),
        new Piece("pawn2", 2, 6, 'pawn', 'black'),
        new Piece("pawn3", 3, 6, 'pawn', 'black'),
        new Piece("pawn4", 4, 6, 'pawn', 'black'),
        new Piece("pawn5", 5, 6, 'pawn', 'black'),
        new Piece("pawn6", 6, 6, 'pawn', 'black'),
        new Piece("pawn7", 7, 6, 'pawn', 'black'),
        new Piece("rook0", 0, 0, 'rook', 'white'),
        new Piece("rook1", 7, 0, 'rook', 'white'),
        new Piece("rook0", 0, 7, 'rook', 'black'),
        new Piece("rook1", 7, 7, 'rook', 'black'),
        new Piece("knight0", 1, 0, 'knight', 'white'),
        new Piece("knight1", 6, 0, 'knight', 'white'),
        new Piece("knight0", 1, 7, 'knight', 'black'),
        new Piece("knight1", 6, 7, 'knight', 'black'),
        new Piece("bishop0", 2, 0, 'bishop', 'white'),
        new Piece("bishop1", 5, 0, 'bishop', 'white'),
        new Piece("bishop0", 2, 7, 'bishop', 'black'),
        new Piece("bishop1", 5, 7, 'bishop', 'black'),
        new Piece("queen0", 3, 0, 'queen', 'white'),
        new Piece("queen0", 3, 7, 'queen', 'black'),
        new Piece("king0", 4, 0, 'king', 'white'),
        new Piece("king0", 4, 7, 'king', 'black')
    );
}

function createTurnOne($user2) {
    $result = callDB(searchCreatedMatch($user2));
    $matchID = mysqli_fetch_array($result)['id'];
    $standardBoard = json_encode(createStandardBoard());
    return "INSERT INTO turn (player, game, boardState) VALUES ('$user2', '$matchID', '$standardBoard')";
}

function putMatch($user1, $user2) {
    callDB(createMatch($user1, $user2));
    deque($user1, $user2);
    callDB(createTurnOne($user1, $user2));
}

function getPossibleMatch($user) {
    return mysqli_fetch_array(callDB(searchMatch($user)));
}

function hasCurrentMatch($user) {
    $data = callDB(searchCreatedMatch($user));
    return mysqli_fetch_array($data);
}


function startSearch($data) {
    $user = $data['user'];
    $matchdata = hasCurrentMatch($user);
    if ($matchdata != null) {
        echo json_encode(array('success' => true, 'match' => $matchdata, $matchdata['id']));
        return;
    }
    enterQueue($user);
    $otherUser = getPossibleMatch($user);
    if ($otherUser == null) {
        echo json_encode(array('success' => true, 'task' => "No other user"));
        return;
    }
    putMatch($user, $otherUser['id']);
    echo json_encode(array('success' => true, 'task' => "Created Match with other user"));
}
