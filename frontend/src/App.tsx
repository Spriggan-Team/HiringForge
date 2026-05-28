import { BrowserRouter, Routes, Route } from 'react-router-dom'

//-- Compoenents
import Login from './pages/Login/page'
import Register from './pages/Register/page'
import EntryPage from './pages/entry'
import RouteScheme from './route.scheme'
import Home from './pages/home/page'
import AppPopup from './layout/components/popup/app.popup'
import AppContextProvider from './context/app.context'


function App() {

  return (
    <AppContextProvider>
      <AppPopup>
        <BrowserRouter>
          <Routes>
            <Route path={RouteScheme.main} element={<EntryPage />} />
            <Route path={RouteScheme.login} element={<Login />} />
            <Route path={RouteScheme.register} element={<Register />} />
            <Route path={RouteScheme.home} element={<Home />} />
          </Routes>
        </BrowserRouter>
      </AppPopup>
    </AppContextProvider>
  )
}

export default App
