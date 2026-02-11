const validUsers = {
    "admin": "1234",
    "user": "password"
};

function validateLogin() {
    const username = document.getElementById("username").value.trim();
    const password = document.getElementById("password").value.trim();
    const errorMsg = document.getElementById("loginError");

    if (!username || !password) {
        errorMsg.textContent = "Please fill in all fields";
        return;
    }

    if (validUsers[username] && validUsers[username] === password) {
        localStorage.setItem("loggedIn", "true");
        localStorage.setItem("username", username);
        document.getElementById("loginPage").style.display = "none";
        document.getElementById("mainApp").style.display = "block";
        errorMsg.textContent = "";
    } else {
        errorMsg.textContent = "Invalid username or password";
    }
}

function handleLoginKeyPress(event) {
    if (event.key === "Enter") {
        validateLogin();
    }
}

function logout() {
    localStorage.removeItem("loggedIn");
    localStorage.removeItem("username");
    document.getElementById("loginPage").style.display = "block";
    document.getElementById("mainApp").style.display = "none";
    document.getElementById("username").value = "";
    document.getElementById("password").value = "";
    document.getElementById("loginError").textContent = "";
}

function checkLogin() {
    if (localStorage.getItem("loggedIn") === "true") {
        document.getElementById("loginPage").style.display = "none";
        document.getElementById("mainApp").style.display = "block";
    }
}

// Check if user is already logged in on page load
window.addEventListener('DOMContentLoaded', checkLogin);

let books = []; // Array (Data Structure)

function addBook() {
    const id = document.getElementById("bookId").value.trim();
    const title = document.getElementById("title").value.trim();
    const author = document.getElementById("author").value.trim();
    const fileInput = document.getElementById("bookDocument");

    if (!id || !title || !author) {
        alert("Please fill all fields");
        return;
    }

    const existing = books.find(book => book.id === id);
    if (existing) {
        alert("Book ID already exists!");
        return;
    }

    const file = fileInput.files[0];
    let fileData = null;
    let fileName = "No Document";

    if (file) {
        fileName = file.name;
        const reader = new FileReader();
        reader.onload = function(e) {
            const bookObj = {
                id: id,
                title: title,
                author: author,
                available: true,
                document: e.target.result,
                fileName: fileName
            };
            books.push(bookObj);
            clearInputs();
            displayBooks();
        };
        reader.readAsDataURL(file);
    } else {
        books.push({
            id: id,
            title: title,
            author: author,
            available: true,
            document: null,
            fileName: "No Document"
        });
        clearInputs();
        displayBooks();
    }
}

function displayBooks() {
    const bookList = document.getElementById("bookList");
    bookList.innerHTML = "";

    books.forEach(book => {
        const row = document.createElement("tr");

        row.innerHTML = `
            <td>${book.id}</td>
            <td>${book.title}</td>
            <td>${book.author}</td>
            <td class="${book.available ? 'status-available' : 'status-borrowed'}">
                ${book.available ? "Available" : "Borrowed"}
            </td>
            <td>
                <button onclick="borrowBook('${book.id}')">Borrow</button>
                <button onclick="returnBook('${book.id}')">Return</button>
                <button onclick="viewDocument('${book.id}')">View Doc</button>
                <button onclick="deleteBook('${book.id}')">Delete</button>
            </td>
        `;

        bookList.appendChild(row);
    });
}

function searchBook() {
    const searchId = document.getElementById("searchId").value.trim();
    const book = books.find(book => book.id === searchId);

    if (!book) {
        alert("Book not found!");
        return;
    }

    alert(
        `Book Found:\n\n` +
        `ID: ${book.id}\n` +
        `Title: ${book.title}\n` +
        `Author: ${book.author}\n` +
        `Status: ${book.available ? "Available" : "Borrowed"}`
    );
}

function borrowBook(id) {
    const book = books.find(book => book.id === id);

    if (!book.available) {
        alert("Book already borrowed!");
        return;
    }

    book.available = false;
    displayBooks();
}

function returnBook(id) {
    const book = books.find(book => book.id === id);

    if (book.available) {
        alert("Book is already available!");
        return;
    }

    book.available = true;
    displayBooks();
}

function viewDocument(id) {
    const book = books.find(book => book.id === id);

    if (!book || !book.document) {
        alert("No document available for this book!");
        return;
    }

    window.open(book.document, '_blank');
}

function deleteBook(id) {
    if (confirm("Are you sure you want to delete this book?")) {
        books = books.filter(book => book.id !== id);
        displayBooks();
    }
}

function clearInputs() {
    document.getElementById("bookId").value = "";
    document.getElementById("title").value = "";
    document.getElementById("author").value = "";
    document.getElementById("bookDocument").value = "";
}