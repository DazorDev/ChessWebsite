//Literally Monad
class DatabaseInput {

    constructor() {
        this.userID = user;
        this.currentSearch = null;
        this.match = null;
        this.chessBoard = null;
        this.setupLeaveButton();
    }

    setupLeaveButton() {
        const button = document.getElementById('leaveButton');
        button.onclick = (ev) => {
            const data = {
                id: this.match.id,
            }
            fetch("../backend/backend.php/?method=disconnect", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            this.match = null;
            this.chessBoard.unload();
            this.chessBoard.loadPieces();
            this.searchForMatch();
        };
    }

    isMoveValid(piece, position) {
        const data = {
            match: this.match.id,
            user: this.userID,
            id: piece.id,
            color: piece.color,
            move: position
        }
        fetch("../backend/backend.php/?method=move", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        }).then(e => e.json()).then(console.log)
    }

    updateBoard() {
        if (!this.match) return null;
        return this.requestBoard()
    }

    requestBoard() {
        //console.log(this.match.id);
        const data = {
            match: this.match.id
        }
        return fetch("../backend/backend.php/?method=update", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        }).then(response => response.json());
    }

    searchForMatch() {
        document.getElementById('state').innerHTML = "Searching..."
        this.currentSearch = setInterval(() => {
            //console.log("trying to connect")
            this.checkForMatch()
                .then(data => {
                    console.log(data);
                    //console.log(data)
                    if (data.match == null) {
                        //console.log("no match found")
                        return;
                    }
                    document.getElementById('state').innerHTML = "Found"
                    //console.log("found match", data.match);
                    this.match = data.match;
                    clearInterval(this.currentSearch);
                });
        }, 1000)
    }

    checkForMatch() {
        const data = {
            user: this.userID,
            match: this.match
        };
        console.log(data)
        return fetch("../backend/backend.php/?method=connect", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        }).then(response => response.json())
    }


}

function startDrag(event) {
    this.piece = event.target;
    this.validSpace = piece.parentNode;
    this.addDragging(this.piece);
}

function stopDrag(event) {
    this.targetSquare = event.target;
    stopDragEvent(this.piece);
    clearClass();
}

class Piece {
    constructor(x, y, type, color, id) {
        this.x = x;
        this.y = y;
        this.type = type;
        this.color = color;
        this.id = id;
        this.obj = null;
    }
}

class ChessBoard {

    constructor(databaseInput) {
        this.squares = document.querySelectorAll('.chessboard .square');
        this.databaseInput = databaseInput;
        this.databaseInput.chessBoard = this;
        this.shouldUpdate = true;
        this.loadPieces();
    }

    loadPieces() {
        this.pieces = createStandardBoard();
        dynamicallyLoad(this);
    }

    unload() {
        this.pieces.forEach(piece => piece.obj.remove());
        this.pieces = [];
    }

    //Literally Monad
    async update() {
        //console.log("updating")
        const data = this.databaseInput.updateBoard();
        if (!data) return;
        data.then(d => {
            console.log(d.state)
            console.log(d.winner)
            console.log(document.getElementById("state").innterHTML);
            if (d.state === "game_over") document.getElementById("state").innerHTML = "Game is over \r\n Winner is : " + d.winner;
            if (d.state === "playing") document.getElementById("state").innerHTML = "Playing";
            if (d.currentPlayer) document.getElementById("player").innerHTML = "Current Player : " + d.currentPlayer;
            const jsonData = JSON.parse(d.gameData);
            //console.log(jsonData);
            //console.log(this.pieces);
            let updated = [];
            for (let checkPiece of jsonData) {
                for (let piece of this.pieces) {
                    if (piece.color != checkPiece.color) continue;
                    if (piece.id != checkPiece.id) continue;
                    updated.push(piece);
                    this.squares[checkPiece.y * 8 + checkPiece.x].appendChild(piece.obj);
                    piece.x = checkPiece.x;
                    piece.y = checkPiece.y;
                    break;
                }
            }
            /*
            for (let i = 0; i < this.pieces.length; i++) {
                let found = false;
                for (let data of updated) {
                    if (this.pieces[i].color == data.color && this.pieces[i].id == data.id) {
                        //console.log(data)
                        found = true;
                        break;
                    }
                }
                if (found) continue;
                console.log(this.pieces[i]);
                this.pieces[i].obj.remove();
                this.pieces.splice(i, 1);
            }
            */
            this.pieces.filter(e => {
                if (updated.includes(e)) return true;
                e.obj.remove();
                return false;
            });
        })
        return;
    }

    move(piece, position) {
        this.databaseInput.isMoveValid(piece, position);
    }

    getPiece(pieceID) {
        pieceID = pieceID.split(" ");
        for (let piece of this.pieces) {
            if (piece.id == pieceID[0] && piece.color == pieceID[1]) {
                return piece;
            }
        }
        return null;
    }

    getPosition(piece) {
        for (let i = 0; i < this.squares.length; i++) {
            if (this.squares[i].contains(piece.obj)) {
                const y = Math.floor(i / 8);
                const x = i % 8;
                return { x: x, y: y };
            }
        }
        return null;
    }
}

// Drag start event listener for chess pieces
function dragStart(event) {
    // Set the data being dragged
    event.dataTransfer.setData('text/plain', event.target.id);

    // Add a CSS class to the dragged piece for styling
    event.target.classList.add('dragged-piece');
}

// Drag over event listener for squares
function dragOver(event) {
    // Prevent default behavior to allow drop
    event.preventDefault();

    // Add a CSS class to the hovered square for styling
    event.target.classList.add('hovered-square');
}

// Drag leave event listener for squares
function dragLeave(event) {
    // Remove the CSS class from the previously hovered square
    event.target.classList.remove('hovered-square');
}

// Drop event listener for squares
function drop(event, chessBoard) {
    // Prevent default behavior
    event.preventDefault();

    // Remove the CSS class from the dropped square
    event.target.classList.remove('hovered-square');

    // Get the dragged chess piece's ID
    const pieceId = event.dataTransfer.getData('text/plain');

    // Add the chess piece to the dropped square
    const piece = document.getElementById(pieceId);
    let square;
    if (event.target.classList.contains('square')) {
        event.target.appendChild(piece);
        square = event.target;
    } else {
        event.target.parentNode.appendChild(piece);
        square = event.target.parentNode;
        event.target.remove()
    }
    piece.classList.remove('dragged-piece');
    const chessPiece = chessBoard.getPiece(pieceId);
    const stringArr = square.id.split(" ");
    const newPos = [stringArr[0], stringArr[1]]
    //console.log(newPos)
    chessBoard.move(chessPiece, newPos);
}

function dynamicallyLoad(chessBoard) {
    let i = 0;
    chessBoard.pieces.forEach(piece => {
        const square = chessBoard.squares[piece.y * 8 + piece.x];
        const pieceObj = document.createElement('img');
        piece.obj = pieceObj;
        const squareSize = square.clientWidth;
        const pieceSize = Math.min(squareSize, square.clientHeight);
        pieceObj.id = piece.id + " " + piece.color;
        pieceObj.style.width = `${pieceSize}px`;
        pieceObj.style.height = `${pieceSize}px`;
        pieceObj.classList.add('piece');
        pieceObj.src = "./pieces/" + piece.type + "_" + piece.color + ".png";
        pieceObj.draggable = true;
        pieceObj.addEventListener('dragstart', dragStart);
        square.appendChild(pieceObj);
    });
    chessBoard.squares.forEach(square => {
        square.addEventListener('dragover', dragOver);
        square.addEventListener('dragleave', dragLeave);
        square.addEventListener('drop', e => drop(e, chessBoard));
    });
}

function createStandardBoard() {
    return [
        new Piece(0, 1, 'pawn', 'white', "pawn0"),
        new Piece(1, 1, 'pawn', 'white', "pawn1"),
        new Piece(2, 1, 'pawn', 'white', "pawn2"),
        new Piece(3, 1, 'pawn', 'white', "pawn3"),
        new Piece(4, 1, 'pawn', 'white', "pawn4"),
        new Piece(5, 1, 'pawn', 'white', "pawn5"),
        new Piece(6, 1, 'pawn', 'white', "pawn6"),
        new Piece(7, 1, 'pawn', 'white', "pawn7"),
        new Piece(0, 6, 'pawn', 'black', "pawn0"),
        new Piece(1, 6, 'pawn', 'black', "pawn1"),
        new Piece(2, 6, 'pawn', 'black', "pawn2"),
        new Piece(3, 6, 'pawn', 'black', "pawn3"),
        new Piece(4, 6, 'pawn', 'black', "pawn4"),
        new Piece(5, 6, 'pawn', 'black', "pawn5"),
        new Piece(6, 6, 'pawn', 'black', "pawn6"),
        new Piece(7, 6, 'pawn', 'black', "pawn7"),
        new Piece(0, 0, 'rook', 'white', "rook0"),
        new Piece(7, 0, 'rook', 'white', "rook1"),
        new Piece(0, 7, 'rook', 'black', "rook0"),
        new Piece(7, 7, 'rook', 'black', "rook1"),
        new Piece(1, 0, 'knight', 'white', "knight0"),
        new Piece(6, 0, 'knight', 'white', "knight1"),
        new Piece(1, 7, 'knight', 'black', "knight0"),
        new Piece(6, 7, 'knight', 'black', "knight1"),
        new Piece(2, 0, 'bishop', 'white', "bishop0"),
        new Piece(5, 0, 'bishop', 'white', "bishop1"),
        new Piece(2, 7, 'bishop', 'black', "bishop0"),
        new Piece(5, 7, 'bishop', 'black', "bishop1"),
        new Piece(3, 0, 'queen', 'white', "queen0"),
        new Piece(3, 7, 'queen', 'black', "queen0"),
        new Piece(4, 0, 'king', 'white', "king0"),
        new Piece(4, 7, 'king', 'black', "king0")
    ];
}

function init() {
    const chessBoard = new ChessBoard(new DatabaseInput());
    chessBoard.update();
    chessBoard.databaseInput.searchForMatch();
    setInterval(() => chessBoard.update(), 1000);
}

document.addEventListener('DOMContentLoaded', init);