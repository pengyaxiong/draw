<style type="text/css">
    /* star */

    #star {
        position: relative;
        width: 600px;
        margin: 20px auto;
        height: 24px;
    }

    #star ul, #star span {
        float: left;
        display: inline;
        height: 19px;
        line-height: 19px;
    }

    #star ul {
        margin: 0 10px;
    }

    #star li {
        float: left;
        width: 24px;
        cursor: pointer;
        text-indent: -9999px;
        background: url(/images/star.png) no-repeat;
    }

    #star strong {
        color: #f60;
        padding-left: 10px;
    }

    #star li.on {
        background-position: 0 -28px;
    }

    #star p {
        position: absolute;
        top: 20px;
        width: 159px;
        height: 60px;
        display: none;
        background: url(/images/icon.gif) no-repeat;
        padding: 7px 10px 0;
    }

    #star p em {
        color: #f60;
        display: block;
        font-style: normal;
    }

</style>

<div class="row">
    <div class="col-sm-6 col-md-6">
        <div class="thumbnail">
            <img src="{{\Storage::disk(config('admin.upload.disk'))->url($comment->product->image)}}"
                 alt="{{$comment->product->name}}">

            <div class="caption">
                <h3>{{$comment->product->name}}</h3>
                <p>{{$comment->product->description}}</p>
                <h4>单价：{{$comment->product->price}} </h4>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-md-6">
        <div class="thumbnail">
            <div class="caption">
                <h2>{{$comment->customer->nickname}}<span
                            style="font-size: 14px;color: #1b6d85">&nbsp;发表于:{{$comment->created_at}}</span></h2>
                @if(!empty($comment->images))
                    @foreach($comment->images as $image)
                        <img src="{{\Storage::disk(config('admin.upload.disk'))->url($image)}}">
                    @endforeach
                @endif
                <p>{{$comment->content}}</p>
                <div id="star">

                    <span>评分</span>

                    <ul>

                        <li><a href="javascript:;">1</a></li>

                        <li><a href="javascript:;">2</a></li>

                        <li><a href="javascript:;">3</a></li>

                        <li><a href="javascript:;">4</a></li>

                        <li><a href="javascript:;">5</a></li>

                    </ul>

                    <span></span>

                    <p></p>

                </div>
                <form action="{{route('admin.shop.reply')}}" method="post">
                    <input type="hidden" name="id" value="{{$comment->id}}">
                    <h3>系统回复:</h3>
                    <textarea class="form-control" name="reply">{{$comment->reply}}</textarea>
                    <br>
                    <p>
                        @if($comment->reply)
                            <a href="javascript:void (0)" onclick="check({{$comment->id}})" class="btn btn-danger"
                               role="button">删除</a>
                        @endif

                        <button type="submit" class="btn btn-primary" role="button">回复</button>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    window.onload = function () {

        var oStar = document.getElementById("star");

        var aLi = oStar.getElementsByTagName("li");

        var oUl = oStar.getElementsByTagName("ul")[0];

        var oSpan = oStar.getElementsByTagName("span")[1];

        var oP = oStar.getElementsByTagName("p")[0];

        var i = iScore = iStar = 0;

        var aMsg = [

            "很不满意|差得太离谱，与卖家描述的严重不符，非常不满",

            "不满意|部分有破损，与卖家描述的不符，不满意",

            "一般|质量一般，没有卖家描述的那么好",

            "满意|质量不错，与卖家描述的基本一致，还是挺满意的",

            "非常满意|质量非常好，与卖家描述的完全一致，非常满意"

        ]

        fnPoint({{$comment->grade}});

        for (i = 1; i <= aLi.length; i++) {

            aLi[i - 1].index = i;


            //鼠标移过显示分数

            aLi[i - 1].onmouseover = function () {

                fnPoint(this.index);

                //浮动层显示

                oP.style.display = "block";

                //计算浮动层位置

                oP.style.left = oUl.offsetLeft + this.index * this.offsetWidth - 104 + "px";

                //匹配浮动层文字内容

                oP.innerHTML = "<em><b>" + this.index + "</b> 分 " + aMsg[this.index - 1].match(/(.+)\|/)[1] + "</em>" + aMsg[this.index - 1].match(/\|(.+)/)[1]

            };


            //鼠标离开后恢复上次评分

            aLi[i - 1].onmouseout = function () {

                fnPoint({{$comment->grade}});

                //关闭浮动层

                oP.style.display = "none"

            };


            //点击后进行评分处理

            aLi[i - 1].onclick = function () {

                iStar = this.index;

                oP.style.display = "none";

                oSpan.innerHTML = "<strong>" + (this.index) + " 分</strong> (" + aMsg[this.index - 1].match(/\|(.+)/)[1] + ")"

            }

        }


        //评分处理

        function fnPoint(iArg) {

            //分数赋值

            iScore = iArg || iStar;

            for (i = 0; i < aLi.length; i++) aLi[i].className = i < iScore ? "on" : "";

        }


    };


    function check(id) {
        swal(
            {
                title: "您确定要删除这条数据吗？",
                text: "{{$alert_text or '删除后仍可恢复，但请谨慎操作！'}}",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "确定删除！",
                cancelButtonText: "取消",
                closeOnConfirm: false,
                closeOnCancel: false,
                preConfirm: function () {
                    $.ajax({
                        method: 'post',
                        url: '/admin/shop/comment_del',
                        data: {id: id},
                        success: function (data) {
                            console.log(data);
                            // $.pjax.reload('#pjax-container');
                            if (data.code) {
                                swal(data.msg, '', 'success');
                                location.href = '/admin/shop/orders'
                            } else {
                                swal(data.msg, '', 'error');
                            }
                        }
                    });
                }
            }
        )
    }
</script>