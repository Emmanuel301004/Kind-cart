body {
            font-family: 'Arial', sans-serif;
            background-color: #f9fafb;
            margin: 0;
            padding: 0;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #2c3e50;
            padding: 15px 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .navbar a {
            color: #ecf0f1;
            text-decoration: none;
            margin-right: 15px;
            font-size: 1rem;
            transition: color 0.3s ease;
        }

        .navbar a:hover {
            color: #bdc3c7;
        }

        .navbar .logout {
            background-color: #e74c3c;
            color: white;
            padding: 8px 15px;
            border-radius: 4px;
            text-transform: uppercase;
        }

        .sell-form {
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .sell-form h1 {
            text-align: center;
            color: #4CAF50;
        }

        .sell-form label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        .sell-form input, 
        .sell-form select, 
        .sell-form button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .sell-form button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }