<?php

require_once "chessboard.php";

function isTurn($game, $user) {
    return getLastTurnUser($game) != $user;
}

function isOver($game) {
    $arr = getGameState($game);
    return $arr['running'] == 0;
}

function isPieceCorrectColor($game, $user, $piece) {
    $state = getGameState($game);
    //Crappy fix for conversion between front and backend
    $correctColor = $state['white'] == $user ? "black" : "white";
    return $correctColor == $piece->color;
}

function capturePiece($pieceID, $color, &$board) {
    for ($i = 0; $i < count($board); $i++) {
        $piece = $board[$i];
        if ($piece->id != $pieceID) continue;
        if ($piece->color != $color) continue;
        array_splice($board, $i, 1);
    }
}

function checkIfPlaceIsFilled($position, $board) {
    return getPieceFromPos($board, $position) != null;
}

function canCapture($piece1, $piece2) {
    return $piece1->color != $piece2->color;
}

function isValidMove($piece, $position, &$board, $gameID, $userID) {
    if (!isTurn($gameID, $userID)) return false;
    if (isOver($gameID)) return false;
    if (!isPieceCorrectColor($gameID, $userID, $piece)) return;
    switch ($piece->type) {
        case 'pawn':
            return pawnMove($piece, $position, $board);
        case 'rook':
            return rookMove($piece, $position, $board);
        case 'queen':
            return queenMove($piece, $position, $board);
        case 'king':
            return kingMove($piece, $position, $board);
        case 'bishop':
            return bishopMove($piece, $position, $board);
        case 'knight':
            return knightMove($piece, $position, $board);
    }
}

function couldMoveTo($piece, $move, &$board) {
    if (checkIfPlaceIsFilled($move, $board)) {
        $capturePiece = getPieceFromPos($board, $move);
        if (canCapture($piece, $capturePiece)) {
            return true;
        };
        return false;
    }
    return true;
}

function moveTo($piece, $move, &$board) {
    if (checkIfPlaceIsFilled($move, $board)) {
        $capturePiece = getPieceFromPos($board, $move);
        if (canCapture($piece, $capturePiece)) {
            capturePiece($capturePiece->id, $capturePiece->color, $board);
            return true;
        };
        return false;
    }
    return true;
}

function moveToPawn($piece, $move, &$board) {
    if (checkIfPlaceIsFilled($move, $board)) {
        $capturePiece = getPieceFromPos($board, $move);
        if (canCapture($piece, $capturePiece)) {
            capturePiece($capturePiece->id, $capturePiece->color, $board);
            return true;
        };
        return false;
    }
    return false;
}

function bishopMove($piece, $move, &$board) {
    $smallestMove = new Position($move->x, $move->y);
    $tempMove = new Position($move->x, $move->y);
    $isPossible = false;
    for ($x = 0; $x != 8; $x++) {
        $tempMove->x = $piece->x < $move->x ? $tempMove->x - 1 : $tempMove->x + 1;
        $tempMove->y = $piece->y < $move->y ? $tempMove->y - 1 : $tempMove->y + 1;
        if (checkIfPlaceIsFilled($tempMove, $board)) {
            if (canCapture($piece, getPieceFromPos($board, $tempMove))) {
                $smallestMove->x = $tempMove->x;
                $smallestMove->y = $tempMove->y;
            }
        }
        if ($tempMove->x == $piece->x && $tempMove->y == $piece->y) {
            $isPossible = true;
            break;
        };
    }
    if (!$isPossible) return false;
    if ($smallestMove->x == $move->x && $smallestMove->y == $move->y) return moveTo($piece, $move, $board);
    return false;
}

function queenMove($piece, $move, &$board) {
    return bishopMove($piece, $move, $board) || rookMove($piece, $move, $board);
}

function kingMove($piece, $move, &$board) {
    if ($piece->x - 1 != $move->x && $piece->x != $move->x && $piece->x + 1 != $move->x) return false;
    if ($piece->y - 1 != $move->y && $piece->y != $move->y && $piece->y + 1 != $move->y) return false;
    return moveTo($piece, $move, $board);
}

function pawnMove($piece, $move, &$board) {
    if ($piece->color == 'black') {
        if ($piece->y == 6) {
            if ($move->y == $piece->y - 1 || $move->y == $piece->y - 2) {
                if ($move->x == $piece->x) return !checkIfPlaceIsFilled($move, $board);
                if ($piece->x + 1 == $move->x || $piece->x - 1 == $move->x) {
                    return moveToPawn($piece, $move, $board);
                }
            }
            return false;
        }
        if ($piece->y - 1 != $move->y) return false;
        if ($piece->x != $move->x) {
            if ($piece->x + 1 == $move->x || $piece->x - 1 == $move->x) {
                return moveToPawn($piece, $move, $board);
            }
            return false;
        }
        return !checkIfPlaceIsFilled($move, $board);
    }

    if ($piece->y == 1) {
        if ($move->y == $piece->y + 1 || $move->y == $piece->y + 2) {
            if ($move->x == $piece->x) return !checkIfPlaceIsFilled($move, $board);
            if ($piece->x + 1 == $move->x || $piece->x - 1 == $move->x) {
                return moveToPawn($piece, $move, $board);
            }
        }
        return false;
    }
    if ($piece->y + 1 != $move->y) return false;
    if ($piece->x != $move->x) {
        if ($piece->x + 1 == $move->x || $piece->x - 1 == $move->x) {
            return moveToPawn($piece, $move, $board);
        }
        return false;
    }
    return !checkIfPlaceIsFilled($move, $board);
}

function knightMove($piece, $move, &$board) {
    if (!(($piece->x + 1 == $move->x && $piece->y + 2 == $move->y)
        || ($piece->x - 1 == $move->x && $piece->y + 2 == $move->y)
        || ($piece->x + 1 == $move->x && $piece->y - 2 == $move->y)
        || ($piece->x - 1 == $move->x && $piece->y - 2 == $move->y)
        || ($piece->y + 1 == $move->y && $piece->x + 2 == $move->x)
        || ($piece->y - 1 == $move->y && $piece->x + 2 == $move->x)
        || ($piece->y + 1 == $move->y && $piece->x - 2 == $move->x)
        || ($piece->y - 1 == $move->y && $piece->x - 2 == $move->x)
    )) return false;
    return moveTo($piece, $move, $board);
}

function rookMove($piece, $move, &$board) {
    $smallestMove = new Position($move->x, $move->y);
    $tempMove = new Position($move->x, $move->y);
    if ($move->x != $piece->x && $move->y != $piece->y) return;
    if ($move->x != $piece->x) {
        for ($x = $move->x; $x != $piece->x; $x = $x > $piece->x ? $x - 1 : $x + 1) {
            $tempMove->x = $x;
            if (checkIfPlaceIsFilled($tempMove, $board)) {
                if (canCapture($piece, getPieceFromPos($board, $tempMove))) {
                    $smallestMove->x = $x;
                    continue;
                }
                if ($x > $piece->x) {
                    $smallestMove->x = $x - 1;
                    continue;
                }
                $smallestMove->x = $x + 1;
            }
        }
        if ($smallestMove->x == $move->x) return moveTo($piece, $move, $board);
        return false;
    }
    for ($y = $move->y; $y != $piece->y; $y = $y > $piece->y ? $y - 1 : $y + 1) {
        $tempMove->y = $y;
        if (checkIfPlaceIsFilled($tempMove, $board)) {
            if (canCapture($piece, getPieceFromPos($board, $tempMove))) {
                $smallestMove->y = $y;
                continue;
            }
            if ($y > $piece->y) {
                $smallestMove->y = $y - 1;
                continue;
            }
            $smallestMove->y = $y + 1;
        }
    }
    if ($smallestMove->y == $move->y) return moveTo($piece, $move, $board);
    return false;
}
