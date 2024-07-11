//import logo from './logo.svg';
import "./Style.css";
import { Routes, Route } from "react-router-dom";
import Header from "./Components/Header/Header";
import Home from "./Components/Home/Home";
import Userlist from "./Components/Userlist/Userlist";
import Adduser from "./Components/Adduser/Adduser";
import Edituser from "./Components/Edituser/Edituser";
import Addproduct from "./Components/Addproduct/Addproduct";
import Productlist from "./Components/Productlist/Productlist";
import Footer from "./Components/Footer/Footer";
import Editproduct from "./Components/Editproduct";

function App() {
  return (
    <div className="App">
      <Header />
      <Routes>
        <Route path="/" element={<Home />} />
        <Route path="/Userlist" element={<Userlist />} />
        <Route path="/Adduser" element={<Adduser />} />
        <Route path="/Edituser/:id" element={<Edituser />} />
        <Route path="/Addproduct/" element={<Addproduct />} />
        <Route path="/Productlist/" element={<Productlist />} />
        <Route path="/Editproduct/" element={<Editproduct />} />
      </Routes>
      <Footer />
    </div>
  );
}

export default App;
