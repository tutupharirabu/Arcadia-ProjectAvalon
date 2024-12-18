import axios from 'axios';

const customFetch = axios.create({
    baseURL: 'https://arcadia-beavalon-development.up.railway.app//api/v1'
  });

  export default customFetch