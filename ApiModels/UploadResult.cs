﻿using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace aevvuploader.ApiModels
{
    class UploadResult
    {
        public int Code { get; set; }
        public string UploadId { get; set; }
        public string Message { get; set; }
    }
}
