document.addEventListener('DOMContentLoaded', function () {
    const loginForm = document.getElementById('loginForm');
    const resetPasswordButton = document.getElementById('forgotPasswordButton');
    const submitResetPasswordButton = document.getElementById('submitResetPasswordButton');
    const resetPasswordForm = document.getElementById('resetPasswordForm');
    const closePopupButton = document.getElementById('closePopup');
    const resultDiv = document.getElementById('resetResult');
    const changePasswordForm = document.getElementById('changePasswordForm'); // Nouveau formulaire pour changer le mot de passe

    // Affichage du pop-up pour la connexion
    const loginPopup = document.getElementById('loginPopup');
    if (loginPopup) {
        document.getElementById('loginButton').addEventListener('click', function () {
            loginPopup.style.display = 'flex'; // Ouvre la fenêtre pop-up
        });
    }

    // Fermeture du pop-up de connexion
    if (closePopupButton) {
        closePopupButton.addEventListener('click', function () {
            loginPopup.style.display = 'none'; // Ferme la fenêtre pop-up
        });
    }
    function parseJwt(token) {
        const base64Url = token.split('.')[1];  // Récupère la partie du payload (au milieu)
        const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');  // Remplace les caractères spéciaux pour base64
        const jsonPayload = decodeURIComponent(atob(base64).split('').map(function(c) {
            return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
        }).join(''));

        return JSON.parse(jsonPayload);  // Retourne le payload décodé en tant qu'objet
    }

    if (loginForm) {
        loginForm.addEventListener('submit', function (event) {
            event.preventDefault();

            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;

            fetch('http://127.0.0.1:8000/api/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({username, password}),
            })
                .then(response => response.json())
                .then(data => {
                    console.log('Réponse complète de l\'API:', data);
                    if (data.token) {
                        const expires = new Date(Date.now() + 7 * 24 * 60 * 60 * 1000); // 1 semaine
                        document.cookie = `authToken=${data.token}; expires=${expires.toUTCString()}; path=/; Secure`;

                        // Extraire le payload du token pour obtenir isTemporaryPassword
                        const payload = parseJwt(data.token);
                        if (payload.isTemporaryPassword) {
                            console.log('Le mot de passe est temporaire.');

                            // Affiche le formulaire de changement de mot de passe
                            if (changePasswordForm) {
                                changePasswordForm.style.display = 'block';
                                document.getElementById('currentPassword').value = password;
                            }
                        } else {
                            location.reload();
                            console.log('Mot de passe non temporaire.');
                        }
                    } else {
                        resultDiv.textContent = 'Erreur de connexion : ' + data.message;
                    }
                })
                .catch(error => {
                    console.error('Erreur lors de la connexion :', error);
                    resultDiv.textContent = 'Une erreur est survenue. Veuillez réessayer.';
                });
        });
    }

    // Afficher le formulaire de réinitialisation du mot de passe
    if (resetPasswordButton) {
        resetPasswordButton.addEventListener('click', function () {
            resetPasswordForm.style.display = 'block'; // Affiche le formulaire de réinitialisation
        });
    }

    // Soumettre le formulaire de réinitialisation du mot de passe
    if (submitResetPasswordButton) {
        submitResetPasswordButton.addEventListener('click', function (event) {
            event.preventDefault();

            const emailReset = document.getElementById('emailReset').value;

            if (!emailReset) {
                resultDiv.textContent = "L'email est requis.";
                return;
            }

            fetch('http://127.0.0.1:8000/api/reset-password', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({email: emailReset}),
            })
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        resultDiv.textContent = data.message;
                    }
                })
                .catch(error => {
                    console.error('Erreur lors de la réinitialisation du mot de passe :', error);
                    resultDiv.textContent = 'Une erreur est survenue. Veuillez réessayer.';
                });
        });
    }

    // Soumettre le formulaire de changement de mot de passe
    const changePasswordButton = document.getElementById('submitChangePasswordButton');
    if (changePasswordButton) {
        changePasswordButton.addEventListener('click', function (event) {
            event.preventDefault();

            const newPassword = document.getElementById('newPassword').value;
            const token = document.cookie.split('=')[1];  // Récupère le token des cookies

            fetch('http://127.0.0.1:8000/api/change-password', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`,
                },
                body: JSON.stringify({newPassword}),
            })
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        resultDiv.textContent = data.message;
                    }
                })
                .catch(error => {
                    console.error('Erreur lors du changement du mot de passe :', error);
                    resultDiv.textContent = 'Une erreur est survenue. Veuillez réessayer.';
                });
        });
    }
});
