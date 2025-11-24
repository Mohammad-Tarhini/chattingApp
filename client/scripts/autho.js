import { signin, signup } from "./component.js";
import { BASE_URL } from "./constants.js";

const container = document.getElementById("container");

const AuthAPI = {

    signup: (data) => axios.post(
        BASE_URL + "auth/signup",
        data, // send JSON
        { headers: { "Content-Type": "application/json" } }
    ),

    signin: (data) => axios.post(
        BASE_URL + "auth/signin",
        data, // send JSON
        { headers: { "Content-Type": "application/json" } }
    ),

};

// SHOW SIGNIN
export function showSignin() {
    container.innerHTML = signin();

    document.getElementById("signinBtn").addEventListener("click", async () => {

        const data = {
            email: document.getElementById("email").value,
            password: document.getElementById("password").value,
        };

        try {
            const res = await AuthAPI.signin(data);

            if (!res.data.success) {
                alert(res.data.message);
                return;
            }

            localStorage.setItem("userId", res.data.data.userId);
            window.location.href = "chat.html";

        } catch (err) {
            const message = err?.response?.data?.message || "Error occurred";
            alert("Error: " + message);
        }
    });

    document.getElementById("toSignup").onclick = showSignup;
}

// SHOW SIGNUP
export function showSignup() {
    container.innerHTML = signup();

    document.getElementById("signupBtn").addEventListener("click", async () => {

        const data = {
            name: document.getElementById("name").value,
            email: document.getElementById("email").value,
            password: document.getElementById("password").value,
        };

        try {
            const res = await AuthAPI.signup(data);
            console.log(res);
            if (!res.data.success) {
                alert(res.data.message);
                return;
            }

            showSignin();

        } catch (err) {
            const message = err?.response?.data?.message || "Error occurred";
            alert("Error: " + message);
        }
    });

    document.getElementById("toSignin").onclick = showSignin;
}

showSignin();
document.getElementById("goSignin").onclick = showSignin;
document.getElementById("goSignup").onclick = showSignup