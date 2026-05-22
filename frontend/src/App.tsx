import { BrowserRouter, Routes, Route } from 'react-router-dom'

//-- Compoenents
import Login from './pages/Login/page'
import Register from './pages/Register/page'
import EntryPage from './pages/entry'
import RouteScheme from './route.scheme'
import Home from './pages/home/page'


function App() {

  return (
    <BrowserRouter>
      <Routes>
        <Route path={RouteScheme.main} element={<EntryPage />} />
        <Route path={RouteScheme.login} element={<Login />} />
        <Route path={RouteScheme.register} element={<Register />} />
        <Route path={RouteScheme.home} element={<Home />} />
      </Routes>
    </BrowserRouter>
  )
}

export default App
